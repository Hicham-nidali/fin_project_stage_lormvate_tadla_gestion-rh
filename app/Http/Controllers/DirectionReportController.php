<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\EvaluationReport;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DirectionReportController extends Controller
{
    public function index(Request $request)
    {
        // Filtres
        $departmentFilter = $request->get('department');
        $typeFilter = $request->get('type');
        $statusFilter = $request->get('status');
        $dateFromFilter = $request->get('date_from');
        $dateToFilter = $request->get('date_to');
        $searchFilter = $request->get('search');

        // Rapports départementaux
        $departmentReports = Report::with(['department', 'creator'])
            ->when($departmentFilter, function($query, $dept) {
                return $query->where('department_id', $dept);
            })
            ->when($typeFilter && $typeFilter !== 'evaluation', function($query, $type) {
                return $query->where('type', $type);
            })
            ->when($statusFilter, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($dateFromFilter, function($query, $date) {
                return $query->where('created_at', '>=', $date);
            })
            ->when($dateToFilter, function($query, $date) {
                return $query->where('created_at', '<=', $date . ' 23:59:59');
            })
            ->when($searchFilter, function($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->when(!$typeFilter || $typeFilter === 'evaluation', function($query) {
                return $query->where('id', '>', 0); // Include all if no filter or not evaluation filter
            }, function($query) {
                return $query; // Include all if type is not evaluation
            });

        // Rapports d'évaluation
        $evaluationReports = EvaluationReport::with(['department', 'creator', 'reviewer'])
            ->when($departmentFilter, function($query, $dept) {
                return $query->where('department_id', $dept);
            })
            ->when($statusFilter, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($dateFromFilter, function($query, $date) {
                return $query->where('created_at', '>=', $date);
            })
            ->when($dateToFilter, function($query, $date) {
                return $query->where('created_at', '<=', $date . ' 23:59:59');
            })
            ->when($searchFilter, function($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->when($typeFilter === 'evaluation' || !$typeFilter, function($query) {
                return $query;
            }, function($query) {
                return $query->where('id', '<', 0); // Exclude if type filter is not evaluation
            });

        // Exécuter les requêtes
        if ($typeFilter === 'evaluation') {
            $departmentReports = collect();
            $evaluationReports = $evaluationReports->get();
        } else {
            $departmentReports = $departmentReports->get();
            if ($typeFilter && $typeFilter !== 'evaluation') {
                $evaluationReports = collect();
            } else {
                $evaluationReports = $evaluationReports->get();
            }
        }

        // Statistiques globales
        $totalReports = Report::count() + EvaluationReport::count();
        $totalDepartmentReports = Report::count();
        $totalEvaluationReports = EvaluationReport::count();
        $reportsThisMonth = Report::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->count() + 
                           EvaluationReport::whereMonth('created_at', now()->month)
                                          ->whereYear('created_at', now()->year)
                                          ->count();

        $departments = Department::all();

        return view('direction.reports.index', compact(
            'departmentReports',
            'evaluationReports',
            'departments',
            'totalReports',
            'totalDepartmentReports',
            'totalEvaluationReports',
            'reportsThisMonth',
            'departmentFilter',
            'typeFilter',
            'statusFilter',
            'dateFromFilter',
            'dateToFilter',
            'searchFilter'
        ));
    }

    public function dashboard()
    {
        // Statistiques globales
        $stats = [
            'total_reports' => Report::count() + EvaluationReport::count(),
            'department_reports' => Report::count(),
            'evaluation_reports' => EvaluationReport::count(),
            'reports_this_month' => Report::whereMonth('created_at', now()->month)->count() + 
                                   EvaluationReport::whereMonth('created_at', now()->month)->count(),
            'published_reports' => Report::where('status', 'published')->count(),
            'draft_reports' => Report::where('status', 'draft')->count() + 
                              EvaluationReport::where('status', 'draft')->count(),
            'pending_reviews' => EvaluationReport::where('status', 'sent')->count()
        ];

        // Rapports par département
        $reportsByDepartment = Department::withCount([
            'reports as department_reports_count',
            'evaluationReports as evaluation_reports_count'
        ])->get()->map(function($dept) {
            $dept->total_reports = $dept->department_reports_count + $dept->evaluation_reports_count;
            return $dept;
        });

        // Rapports récents (tous types confondus)
        $recentDepartmentReports = Report::with(['department', 'creator'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($report) {
                $report->report_type = 'department';
                return $report;
            });

        $recentEvaluationReports = EvaluationReport::with(['department', 'creator'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($report) {
                $report->report_type = 'evaluation';
                return $report;
            });

        $recentReports = $recentDepartmentReports->concat($recentEvaluationReports)
            ->sortByDesc('created_at')
            ->take(10);

        // Évolution mensuelle
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyStats[] = [
                'month' => $date->format('M Y'),
                'department_reports' => Report::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'evaluation_reports' => EvaluationReport::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count()
            ];
        }

        // Rapports par statut
        $statusStats = [
            'published' => Report::where('status', 'published')->count(),
            'draft' => Report::where('status', 'draft')->count() + 
                      EvaluationReport::where('status', 'draft')->count(),
            'sent' => EvaluationReport::where('status', 'sent')->count(),
            'reviewed' => EvaluationReport::where('status', 'reviewed')->count()
        ];

        return view('direction.reports.dashboard', compact(
            'stats',
            'reportsByDepartment',
            'recentReports',
            'monthlyStats',
            'statusStats'
        ));
    }

    public function showDepartmentReport($id)
    {
        $report = Report::with(['department', 'creator'])->findOrFail($id);
        return view('direction.reports.show-department', compact('report'));
    }

    public function showEvaluationReport($id)
    {
        $report = EvaluationReport::with(['department', 'creator', 'reviewer'])->findOrFail($id);
        return view('direction.reports.show-evaluation', compact('report'));
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'all'); // all, department, evaluation
        $format = $request->get('format', 'csv'); // csv, excel

        $data = [];

        if ($type === 'all' || $type === 'department') {
            $departmentReports = Report::with(['department', 'creator'])->get();
            foreach ($departmentReports as $report) {
                $data[] = [
                    'Type' => 'Rapport Départemental',
                    'Titre' => $report->title,
                    'Département' => $report->department->name,
                    'Créé par' => $report->creator->name,
                    'Type de rapport' => ucfirst($report->type),
                    'Statut' => ucfirst($report->status),
                    'Période début' => $report->period_start ? $report->period_start->format('d/m/Y') : '',
                    'Période fin' => $report->period_end ? $report->period_end->format('d/m/Y') : '',
                    'Date création' => $report->created_at->format('d/m/Y H:i'),
                ];
            }
        }

        if ($type === 'all' || $type === 'evaluation') {
            $evaluationReports = EvaluationReport::with(['department', 'creator', 'reviewer'])->get();
            foreach ($evaluationReports as $report) {
                $data[] = [
                    'Type' => 'Rapport d\'Évaluation',
                    'Titre' => $report->title,
                    'Département' => $report->department->name,
                    'Créé par' => $report->creator->name,
                    'Type de rapport' => 'Évaluation',
                    'Statut' => ucfirst($report->status),
                    'Période début' => $report->evaluation_period_start->format('d/m/Y'),
                    'Période fin' => $report->evaluation_period_end->format('d/m/Y'),
                    'Date création' => $report->created_at->format('d/m/Y H:i'),
                    'Envoyé le' => $report->sent_at ? $report->sent_at->format('d/m/Y H:i') : '',
                    'Examiné par' => $report->reviewer ? $report->reviewer->name : '',
                    'Examiné le' => $report->reviewed_at ? $report->reviewed_at->format('d/m/Y H:i') : '',
                ];
            }
        }

        if ($format === 'csv') {
            return $this->exportToCsv($data, $type);
        }

        // Pour l'Excel, on peut ajouter une bibliothèque comme PhpSpreadsheet
        return $this->exportToCsv($data, $type);
    }

    private function exportToCsv($data, $type)
    {
        $filename = 'rapports_' . $type . '_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // BOM pour UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            if (!empty($data)) {
                // En-têtes
                fputcsv($file, array_keys($data[0]), ';');
                
                // Données
                foreach ($data as $row) {
                    fputcsv($file, $row, ';');
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function reportsByStatus()
    {
        $statusData = [
            'published' => Report::where('status', 'published')->count(),
            'draft' => Report::where('status', 'draft')->count() + 
                      EvaluationReport::where('status', 'draft')->count(),
            'sent' => EvaluationReport::where('status', 'sent')->count(),
            'reviewed' => EvaluationReport::where('status', 'reviewed')->count()
        ];

        return response()->json($statusData);
    }

    public function reportsByDepartment()
    {
        $departmentData = Department::withCount([
            'reports as department_reports_count',
            'evaluationReports as evaluation_reports_count'
        ])->get()->map(function($dept) {
            return [
                'name' => $dept->name,
                'department_reports' => $dept->department_reports_count,
                'evaluation_reports' => $dept->evaluation_reports_count,
                'total' => $dept->department_reports_count + $dept->evaluation_reports_count
            ];
        });

        return response()->json($departmentData);
    }

    public function monthlyTrends()
    {
        $trends = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $trends[] = [
                'month' => $date->format('Y-m'),
                'label' => $date->format('M Y'),
                'department_reports' => Report::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'evaluation_reports' => EvaluationReport::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count()
            ];
        }

        return response()->json($trends);
    }
}