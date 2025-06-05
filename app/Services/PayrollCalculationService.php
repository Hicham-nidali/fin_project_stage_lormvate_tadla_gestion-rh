<?php
namespace App\Services;

use App\Models\PayrollRecord;
use App\Models\EmployeeSalary;
use App\Models\EvaluationReport;
use App\Models\User;
use App\Models\OvertimeRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollCalculationService
{
    const MAX_POSITIVE_ADJUSTMENT = 20;
    const MAX_NEGATIVE_ADJUSTMENT = -15;
    
    const OVERTIME_RATE_MULTIPLIER = 1.5;
    const STANDARD_WORK_HOURS = 8;
    const STANDARD_WORK_DAYS = 22;
    const DEDUCTION_RATE = 0.25;

    public function calculatePayrollForEmployee($userId, $period, $evaluationReportId = null)
    {
        $user = User::findOrFail($userId);
        $periodDate = Carbon::createFromFormat('Y-m', $period);
        
        $salary = EmployeeSalary::getSalaryForUserAtDate($userId, $periodDate);
        if (!$salary) {
            throw new \Exception("Aucun salaire défini pour l'employé {$user->name} en {$period}");
        }

        $evaluationReport = null;
        if ($evaluationReportId) {
            $evaluationReport = EvaluationReport::findOrFail($evaluationReportId);
        } else {
            $evaluationReport = $this->findEvaluationReportForPeriod($userId, $periodDate);
        }

        $overtimeData = $this->getOvertimeDataForPeriod($userId, $periodDate);
        $adjustmentData = $this->calculatePerformanceAdjustment($evaluationReport, $overtimeData);

        $baseSalary = $salary->base_salary;
        $adjustmentAmount = $baseSalary * ($adjustmentData['percentage'] / 100);
        $overtimeAmount = $overtimeData['total_amount'];
        $grossSalary = $baseSalary + $adjustmentAmount + $overtimeAmount;
        $deductions = $grossSalary * self::DEDUCTION_RATE;
        $netSalary = $grossSalary - $deductions;

        $payrollRecord = PayrollRecord::updateOrCreate(
            [
                'user_id' => $userId,
                'period' => $period
            ],
            [
                'department_id' => $user->department_id,
                'evaluation_report_id' => $evaluationReport?->id,
                'period_start' => $periodDate->copy()->startOfMonth()->toDateString(),
                'period_end' => $periodDate->copy()->endOfMonth()->toDateString(),
                'base_salary' => $baseSalary,
                'adjustment_percentage' => $adjustmentData['percentage'],
                'adjustment_amount' => $adjustmentAmount,
                'overtime_hours' => $overtimeData['total_hours'],
                'overtime_rate' => $overtimeData['hourly_rate'],
                'overtime_amount' => $overtimeAmount,
                'gross_salary' => $grossSalary,
                'deductions' => $deductions,
                'net_salary' => $netSalary,
                'performance_data' => $adjustmentData['performance_data'],
                'adjustment_details' => $adjustmentData['details'],
                'status' => 'calculated',
                'calculated_at' => now(),
                'calculated_by' => auth()->id()
            ]
        );

        Log::info("Paie calculée pour {$user->name} - Période: {$period} - Net: {$netSalary} DH"); // Changé EUR à DH

        return $payrollRecord;
    }

    public function calculatePayrollForDepartment($departmentId, $period)
    {
        $employees = User::where('department_id', $departmentId)
                        ->where('role', 'employee')
                        ->get();

        $results = ['success' => [], 'errors' => []];
        
        foreach ($employees as $employee) {
            try {
                $result = $this->calculatePayrollForEmployee($employee->id, $period);
                $results['success'][] = $result;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'employee' => $employee->name,
                    'error' => $e->getMessage()
                ];
                Log::error("Erreur calcul paie {$employee->name}: " . $e->getMessage());
            }
        }

        return $results;
    }

    public function calculatePayrollForAllEmployees($period)
    {
        $employees = User::where('role', 'employee')
                        ->whereNotNull('department_id')
                        ->get();

        $results = ['success' => [], 'errors' => []];
        
        foreach ($employees as $employee) {
            try {
                $result = $this->calculatePayrollForEmployee($employee->id, $period);
                $results['success'][] = $result;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'employee' => $employee->name,
                    'error' => $e->getMessage()
                ];
                Log::error("Erreur calcul paie {$employee->name}: " . $e->getMessage());
            }
        }

        return $results;
    }

    private function calculatePerformanceAdjustment($evaluationReport, $overtimeData)
    {
        if (!$evaluationReport) {
            return [
                'percentage' => 0,
                'details' => ['reason' => 'Aucun rapport d\'évaluation disponible'],
                'performance_data' => []
            ];
        }

        $performanceData = $evaluationReport->employees_performance;
        if (empty($performanceData)) {
            return [
                'percentage' => 0,
                'details' => ['reason' => 'Aucune donnée de performance dans le rapport'],
                'performance_data' => []
            ];
        }

        $avgScore = collect($performanceData)->avg('overall_score');
        $avgAttendance = collect($performanceData)->avg('attendance_score');
        $avgTasks = collect($performanceData)->avg('tasks_score');

        $adjustment = 0;
        $details = [];

        if ($avgScore >= 90 && $avgAttendance >= 95 && $overtimeData['total_hours'] > 0) {
            $adjustment = 20;
            $details[] = 'Performance excellente (≥90%) + présence excellente (≥95%) + heures supplémentaires';
        } elseif ($avgScore >= 85 && $avgAttendance >= 90 && $avgTasks >= 90) {
            $adjustment = 15;
            $details[] = 'Très bonne performance (≥85%) + bonne présence (≥90%) + excellentes tâches (≥90%)';
        } elseif ($avgScore >= 80 && $avgAttendance >= 85) {
            $adjustment = 10;
            $details[] = 'Bonne performance (≥80%) + présence correcte (≥85%)';
        }
        elseif ($avgScore < 60 || $avgAttendance < 70) {
            if ($avgScore < 50 && $avgAttendance < 60) {
                $adjustment = -15;
                $details[] = 'Performance insuffisante (<50%) + présence faible (<60%)';
            } elseif ($avgScore < 55 || $avgAttendance < 65) {
                $adjustment = -10;
                $details[] = 'Performance ou présence insuffisante';
            } else {
                $adjustment = -5;
                $details[] = 'Performance en dessous des attentes';
            }
        }

        if ($overtimeData['total_hours'] > 20 && $adjustment >= 0) {
            $adjustment = min($adjustment + 5, self::MAX_POSITIVE_ADJUSTMENT);
            $details[] = 'Bonus heures supplémentaires importantes (>20h)';
        }

        $adjustment = max(self::MAX_NEGATIVE_ADJUSTMENT, min(self::MAX_POSITIVE_ADJUSTMENT, $adjustment));

        return [
            'percentage' => $adjustment,
            'details' => $details,
            'performance_data' => [
                'overall_score' => $avgScore,
                'attendance_rate' => $avgAttendance,
                'task_completion_rate' => $avgTasks,
                'employees_count' => count($performanceData)
            ]
        ];
    }

    private function getOvertimeDataForPeriod($userId, $periodDate)
    {
        $startDate = $periodDate->copy()->startOfMonth();
        $endDate = $periodDate->copy()->endOfMonth();

        // Si la table overtime_records n'existe pas encore, retourner des valeurs par défaut
        try {
            $overtimeRecords = OvertimeRecord::where('user_id', $userId)
                                           ->where('status', 'approved')
                                           ->whereBetween('overtime_date', [$startDate, $endDate])
                                           ->get();
            $totalHours = $overtimeRecords->sum('hours_approved');
        } catch (\Exception $e) {
            // Table n'existe pas ou autre erreur
            $totalHours = 0;
        }
        
        $salary = EmployeeSalary::getSalaryForUserAtDate($userId, $periodDate);
        $hourlyRate = $salary ? 
            ($salary->base_salary / (self::STANDARD_WORK_DAYS * self::STANDARD_WORK_HOURS)) : 0;
        
        $overtimeRate = $hourlyRate * self::OVERTIME_RATE_MULTIPLIER;
        $totalAmount = $totalHours * $overtimeRate;

        return [
            'total_hours' => $totalHours,
            'hourly_rate' => $overtimeRate,
            'total_amount' => $totalAmount,
            'records_count' => isset($overtimeRecords) ? $overtimeRecords->count() : 0
        ];
    }

    private function findEvaluationReportForPeriod($userId, $periodDate)
    {
        $user = User::findOrFail($userId);
        
        try {
            return EvaluationReport::where('department_id', $user->department_id)
                                  ->where('status', 'reviewed')
                                  ->where('evaluation_period_start', '<=', $periodDate->endOfMonth())
                                  ->where('evaluation_period_end', '>=', $periodDate->startOfMonth())
                                  ->orderBy('created_at', 'desc')
                                  ->first();
        } catch (\Exception $e) {
            // Table n'existe pas ou autre erreur
            return null;
        }
    }

    public function approvePayroll($payrollId)
    {
        $payroll = PayrollRecord::findOrFail($payrollId);
        
        if ($payroll->status !== 'calculated') {
            throw new \Exception('Seuls les bulletins calculés peuvent être approuvés');
        }

        $payroll->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id()
        ]);

        Log::info("Bulletin de paie approuvé: {$payroll->id} pour {$payroll->user->name}");

        return $payroll;
    }

    public function markAsPaid($payrollId, $paymentReference = null)
    {
        $payroll = PayrollRecord::findOrFail($payrollId);
        
        if ($payroll->status !== 'approved') {
            throw new \Exception('Seuls les bulletins approuvés peuvent être marqués comme payés');
        }

        $payroll->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_reference' => $paymentReference
        ]);

        Log::info("Bulletin de paie marqué comme payé: {$payroll->id} - Ref: {$paymentReference}");

        return $payroll;
    }

    public function getPayrollSummary($period, $departmentId = null)
    {
        $query = PayrollRecord::where('period', $period);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $records = $query->with('user', 'department')->get();

        return [
            'total_employees' => $records->count(),
            'total_gross' => $records->sum('gross_salary'),
            'total_net' => $records->sum('net_salary'),
            'total_adjustments' => $records->sum('adjustment_amount'),
            'total_overtime' => $records->sum('overtime_amount'),
            'total_deductions' => $records->sum('deductions'),
            'by_status' => $records->groupBy('status')->map->count(),
            'positive_adjustments' => $records->where('adjustment_percentage', '>', 0)->count(),
            'negative_adjustments' => $records->where('adjustment_percentage', '<', 0)->count(),
            'avg_performance' => $records->avg(function($record) {
                return $record->performance_data['overall_score'] ?? 0;
            })
        ];
    }
}