<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeSalary;
use App\Models\PayrollRecord;
use App\Models\User;
use App\Models\Department;
use App\Models\EvaluationReport;
use App\Services\PayrollCalculationService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HRPayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollCalculationService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function dashboard()
    {
        $currentPeriod = PayrollRecord::generatePeriod();
        $summary = $this->payrollService->getPayrollSummary($currentPeriod);
        
        $recentPayrolls = PayrollRecord::with(['user', 'department'])
                                     ->where('period', $currentPeriod)
                                     ->orderBy('created_at', 'desc')
                                     ->take(10)
                                     ->get();

        $pendingApprovals = PayrollRecord::where('status', 'calculated')->count();
        $totalEmployees = User::where('role', 'employee')->count();
        $employeesWithSalary = EmployeeSalary::whereHas('user', function($q) {
            $q->where('role', 'employee');
        })->where('effective_from', '<=', now())
        ->where(function($q) {
            $q->whereNull('effective_to')->orWhere('effective_to', '>=', now());
        })->distinct('user_id')->count();

        return view('hr.payroll.dashboard', compact(
            'summary', 'recentPayrolls', 'pendingApprovals', 
            'totalEmployees', 'employeesWithSalary', 'currentPeriod'
        ));
    }

    public function salariesIndex()
    {
        $salaries = EmployeeSalary::with(['user', 'department', 'creator'])
                                 ->whereHas('user', function($q) {
                                     $q->where('role', 'employee');
                                 })
                                 ->where('effective_from', '<=', now())
                                 ->where(function($q) {
                                     $q->whereNull('effective_to')->orWhere('effective_to', '>=', now());
                                 })
                                 ->orderBy('created_at', 'desc')
                                 ->get();
        
        $departments = Department::all();
        $employeesWithoutSalary = User::where('role', 'employee')
                                     ->whereDoesntHave('salaries', function($q) {
                                         $q->where('effective_from', '<=', now())
                                           ->where(function($q2) {
                                               $q2->whereNull('effective_to')->orWhere('effective_to', '>=', now());
                                           });
                                     })
                                     ->get();

        return view('hr.payroll.salaries.index', compact(
            'salaries', 'departments', 'employeesWithoutSalary'
        ));
    }

    public function salariesCreate()
    {
        $employees = User::where('role', 'employee')->with('department')->get();
        $departments = Department::all();

        return view('hr.payroll.salaries.create', compact('employees', 'departments'));
    }

    public function salariesStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'base_salary' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'notes' => 'nullable|string'
        ]);

        $user = User::findOrFail($request->user_id);

        if ($request->effective_from <= now()) {
            EmployeeSalary::where('user_id', $request->user_id)
                         ->where('effective_from', '<=', now())
                         ->where(function($q) {
                             $q->whereNull('effective_to')->orWhere('effective_to', '>=', now());
                         })
                         ->update(['effective_to' => Carbon::parse($request->effective_from)->subDay()]);
        }

        EmployeeSalary::create([
            'user_id' => $request->user_id,
            'department_id' => $user->department_id,
            'base_salary' => $request->base_salary,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
            'notes' => $request->notes,
            'created_by' => auth()->id()
        ]);

        return redirect()->route('hr.payroll.salaries.index')
                        ->with('success', 'Salaire créé avec succès');
    }

    public function salariesEdit($id)
    {
        $salary = EmployeeSalary::with(['user', 'department'])->findOrFail($id);
        $employees = User::where('role', 'employee')->with('department')->get();
        $departments = Department::all();

        return view('hr.payroll.salaries.edit', compact('salary', 'employees', 'departments'));
    }

    public function salariesUpdate(Request $request, $id)
    {
        $salary = EmployeeSalary::findOrFail($id);
        
        $request->validate([
            'base_salary' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'notes' => 'nullable|string'
        ]);

        $salary->update([
            'base_salary' => $request->base_salary,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
            'notes' => $request->notes
        ]);

        return redirect()->route('hr.payroll.salaries.index')
                        ->with('success', 'Salaire modifié avec succès');
    }

    public function payrollIndex(Request $request)
    {
        $period = $request->get('period', PayrollRecord::generatePeriod());
        $department = $request->get('department');
        $status = $request->get('status');

        $query = PayrollRecord::with(['user', 'department', 'evaluationReport'])
                             ->where('period', $period);

        if ($department) {
            $query->where('department_id', $department);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $payrolls = $query->orderBy('created_at', 'desc')->get();
        $departments = Department::all();
        $availablePeriods = PayrollRecord::select('period')
                                        ->distinct()
                                        ->orderBy('period', 'desc')
                                        ->limit(24)
                                        ->pluck('period')
                                        ->toArray();
        $summary = $this->payrollService->getPayrollSummary($period, $department);

        return view('hr.payroll.index', compact(
            'payrolls', 'departments', 'availablePeriods', 'period', 
            'department', 'status', 'summary'
        ));
    }

    public function calculatePayroll(Request $request)
    {
        $request->validate([
            'period' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            $results = ['success' => [], 'errors' => []];

            if ($request->user_ids) {
                foreach ($request->user_ids as $userId) {
                    try {
                        $result = $this->payrollService->calculatePayrollForEmployee($userId, $request->period);
                        $results['success'][] = $result;
                    } catch (\Exception $e) {
                        $user = User::find($userId);
                        $results['errors'][] = [
                            'employee' => $user?->name ?? "ID: {$userId}",
                            'error' => $e->getMessage()
                        ];
                    }
                }
            } elseif ($request->department_id) {
                $results = $this->payrollService->calculatePayrollForDepartment(
                    $request->department_id, 
                    $request->period
                );
            } else {
                $results = $this->payrollService->calculatePayrollForAllEmployees($request->period);
            }

            DB::commit();

            $successCount = count($results['success'] ?? []);
            $errorCount = count($results['errors'] ?? []);

            $message = $successCount . ' bulletins calculés avec succès';
            if ($errorCount > 0) {
                $message .= ', ' . $errorCount . ' erreurs';
            }

            return redirect()->route('hr.payroll.index', ['period' => $request->period])
                           ->with('success', $message)
                           ->with('calculation_errors', $results['errors'] ?? []);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors du calcul: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $payroll = PayrollRecord::with(['user', 'department', 'evaluationReport', 'calculator', 'approver'])
                               ->findOrFail($id);

        return view('hr.payroll.show', compact('payroll'));
    }

    public function approve($id)
    {
        try {
            $payroll = $this->payrollService->approvePayroll($id);
            
            return redirect()->route('hr.payroll.show', $id)
                           ->with('success', 'Bulletin de paie approuvé avec succès');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markAsPaid(Request $request, $id)
    {
        $request->validate([
            'payment_reference' => 'nullable|string|max:255'
        ]);

        try {
            $payroll = $this->payrollService->markAsPaid($id, $request->payment_reference);
            
            return redirect()->route('hr.payroll.show', $id)
                           ->with('success', 'Bulletin marqué comme payé');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'payroll_ids' => 'required|array',
            'payroll_ids.*' => 'exists:payroll_records,id'
        ]);

        $approved = 0;
        $errors = [];

        foreach ($request->payroll_ids as $id) {
            try {
                $this->payrollService->approvePayroll($id);
                $approved++;
            } catch (\Exception $e) {
                $payroll = PayrollRecord::find($id);
                $errors[] = ($payroll?->user?->name ?? "ID: {$id}") . ': ' . $e->getMessage();
            }
        }

        $message = "{$approved} bulletin(s) approuvé(s)";
        if (!empty($errors)) {
            $message .= ', ' . count($errors) . ' erreur(s)';
        }

        return back()->with('success', $message)
                    ->with('bulk_errors', $errors);
    }

    public function export(Request $request)
    {
        $period = $request->get('period', PayrollRecord::generatePeriod());
        $department = $request->get('department');

        $query = PayrollRecord::with(['user', 'department'])
                             ->where('period', $period);

        if ($department) {
            $query->where('department_id', $department);
        }

        $payrolls = $query->get();

        $filename = "bulletins_paie_{$period}" . ($department ? "_dept_{$department}" : '') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($payrolls) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Employé', 'Département', 'Période', 'Salaire de base (DH)', 'Ajustement %',
                'Montant ajustement (DH)', 'Heures sup.', 'Montant heures sup. (DH)', 'Salaire brut (DH)',
                'Déductions (DH)', 'Salaire net (DH)', 'Statut'
            ]);

            foreach ($payrolls as $payroll) {
                fputcsv($file, [
                    $payroll->user->name,
                    $payroll->department->name,
                    $payroll->formatted_period,
                    $payroll->base_salary,
                    $payroll->adjustment_percentage,
                    $payroll->adjustment_amount,
                    $payroll->overtime_hours,
                    $payroll->overtime_amount,
                    $payroll->gross_salary,
                    $payroll->deductions,
                    $payroll->net_salary,
                    $payroll->status_label
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function reports()
    {
        $periods = PayrollRecord::select('period')
                               ->distinct()
                               ->orderBy('period', 'desc')
                               ->limit(12)
                               ->pluck('period')
                               ->toArray();
        $departments = Department::withCount('users')->get();
        
        $stats = [];
        foreach ($periods as $period) {
            $stats[$period] = $this->payrollService->getPayrollSummary($period);
        }

        return view('hr.payroll.reports', compact('periods', 'departments', 'stats'));
    }
}