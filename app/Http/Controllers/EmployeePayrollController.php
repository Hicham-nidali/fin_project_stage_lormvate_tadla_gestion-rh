<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PayrollRecord;
use App\Models\EmployeeSalary;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeePayrollController extends Controller
{
    public function index()
    {
        $employee = Auth::user();
        
        $recentPayrolls = PayrollRecord::where('user_id', $employee->id)
                                     ->with('department')
                                     ->where('period_start', '>=', now()->subMonths(12))
                                     ->orderBy('period', 'desc')
                                     ->get();

        $currentSalary = EmployeeSalary::getCurrentSalaryForUser($employee->id);
        
        $currentPeriod = PayrollRecord::generatePeriod();
        $currentPayroll = PayrollRecord::where('user_id', $employee->id)
                                      ->where('period', $currentPeriod)
                                      ->first();

        $stats = [
            'total_payrolls' => $recentPayrolls->count(),
            'avg_net_salary' => $recentPayrolls->avg('net_salary'),
            'total_overtime' => $recentPayrolls->sum('overtime_amount'),
            'positive_adjustments' => $recentPayrolls->where('adjustment_percentage', '>', 0)->count(),
            'negative_adjustments' => $recentPayrolls->where('adjustment_percentage', '<', 0)->count()
        ];

        return view('employee.payroll.index', compact(
            'recentPayrolls', 'currentSalary', 'currentPayroll', 'stats'
        ));
    }

    public function show($id)
    {
        $employee = Auth::user();
        
        $payroll = PayrollRecord::where('user_id', $employee->id)
                               ->with(['department', 'evaluationReport'])
                               ->findOrFail($id);

        return view('employee.payroll.show', compact('payroll'));
    }

    public function history(Request $request)
    {
        $employee = Auth::user();
        $year = $request->get('year', now()->year);
        
        $payrolls = PayrollRecord::where('user_id', $employee->id)
                                ->whereYear('period_start', $year)
                                ->with('department')
                                ->orderBy('period', 'desc')
                                ->get();

        $availableYears = PayrollRecord::where('user_id', $employee->id)
                                      ->selectRaw('DISTINCT YEAR(period_start) as year')
                                      ->orderBy('year', 'desc')
                                      ->pluck('year')
                                      ->toArray();

        $yearStats = [
            'total_gross' => $payrolls->sum('gross_salary'),
            'total_net' => $payrolls->sum('net_salary'),
            'total_overtime' => $payrolls->sum('overtime_amount'),
            'total_adjustments' => $payrolls->sum('adjustment_amount'),
            'avg_performance' => $payrolls->avg(function($record) {
                return $record->performance_data['overall_score'] ?? 0;
            })
        ];

        return view('employee.payroll.history', compact(
            'payrolls', 'availableYears', 'year', 'yearStats'
        ));
    }

    public function download($id)
    {
        $employee = Auth::user();
        
        $payroll = PayrollRecord::where('user_id', $employee->id)
                               ->with(['department', 'evaluationReport'])
                               ->findOrFail($id);

        // Nettoyer le nom de l'employé pour le nom du fichier
        $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $employee->name);
        $filename = "Bulletin_Paie_{$payroll->period}_{$cleanName}.html";
        
        // Générer le contenu HTML du bulletin
        $html = view('employee.payroll.pdf', compact('payroll'))->render();
        
        // Retourner le fichier avec forçage du téléchargement
        return response($html)
                ->header('Content-Type', 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Transfer-Encoding', 'binary')
                ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                ->header('Pragma', 'public')
                ->header('Expires', '0');
    }

    public function performanceDetails($id)
    {
        $employee = Auth::user();
        
        $payroll = PayrollRecord::where('user_id', $employee->id)
                               ->with('evaluationReport')
                               ->findOrFail($id);

        $performanceData = $payroll->performance_data ?? [];
        $adjustmentDetails = $payroll->adjustment_details ?? [];

        return response()->json([
            'performance_data' => $performanceData,
            'adjustment_details' => $adjustmentDetails,
            'evaluation_report' => $payroll->evaluationReport ? [
                'title' => $payroll->evaluationReport->title,
                'period' => $payroll->evaluationReport->evaluation_period_start->format('m/Y'),
                'summary' => $payroll->evaluationReport->summary
            ] : null
        ]);
    }

    public function compare(Request $request)
    {
        $employee = Auth::user();
        $periods = $request->get('periods', []);
        
        if (empty($periods)) {
            $periods = PayrollRecord::where('user_id', $employee->id)
                                   ->orderBy('period', 'desc')
                                   ->take(6)
                                   ->pluck('period')
                                   ->toArray();
        }

        $payrolls = PayrollRecord::where('user_id', $employee->id)
                                ->whereIn('period', $periods)
                                ->orderBy('period', 'desc')
                                ->get();

        $chartData = [
            'periods' => $payrolls->pluck('formatted_period')->toArray(),
            'net_salary' => $payrolls->pluck('net_salary')->toArray(),
            'gross_salary' => $payrolls->pluck('gross_salary')->toArray(),
            'adjustments' => $payrolls->pluck('adjustment_amount')->toArray(),
            'overtime' => $payrolls->pluck('overtime_amount')->toArray(),
            'performance' => $payrolls->map(function($p) {
                return $p->performance_data['overall_score'] ?? 0;
            })->toArray()
        ];

        return view('employee.payroll.compare', compact('payrolls', 'chartData'));
    }
}