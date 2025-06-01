<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EvaluationReport;
use App\Models\User;
use App\Models\Task;
use App\Models\Attendance;
use App\Models\Request as DepartmentRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvaluationReportController extends Controller
{
    public function index()
    {
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        if (!$departmentId) {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }
        
        $reports = EvaluationReport::where('department_id', $departmentId)
                                 ->with('creator')
                                 ->orderBy('created_at', 'desc')
                                 ->get();
        
        return view('evaluation-reports.index', compact('reports'));
    }

    public function create()
    {
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        if (!$departmentId) {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }

        // Période par défaut : mois précédent
        $defaultStart = Carbon::now()->startOfMonth()->subMonth();
        $defaultEnd = Carbon::now()->startOfMonth()->subDay();
        
        return view('evaluation-reports.create', compact('defaultStart', 'defaultEnd'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'required|string',
            'evaluation_period_start' => 'required|date',
            'evaluation_period_end' => 'required|date|after:evaluation_period_start',
            'recommendations' => 'nullable|string',
        ]);

        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;

        // Générer les données automatiquement
        $periodStart = Carbon::parse($request->evaluation_period_start);
        $periodEnd = Carbon::parse($request->evaluation_period_end);
        
        $reportData = $this->generateReportData($departmentId, $periodStart, $periodEnd);

        EvaluationReport::create([
            'title' => $request->title,
            'summary' => $request->summary,
            'department_id' => $departmentId,
            'created_by' => $departmentHead->id,
            'evaluation_period_start' => $periodStart,
            'evaluation_period_end' => $periodEnd,
            'attendance_data' => $reportData['attendance'],
            'tasks_data' => $reportData['tasks'],
            'requests_data' => $reportData['requests'],
            'employees_performance' => $reportData['performance'],
            'recommendations' => $request->recommendations,
            'status' => 'draft',
        ]);

        return redirect()->route('evaluation-reports.index')
                        ->with('success', 'Rapport d\'évaluation créé avec succès');
    }

    public function show($id)
    {
        $report = EvaluationReport::with(['department', 'creator', 'reviewer'])->findOrFail($id);
        
        // Vérifier que l'utilisateur peut voir ce rapport
        if (Auth::user()->role == 'department_head' && $report->department_id != Auth::user()->department_id) {
            abort(403);
        }
        
        return view('evaluation-reports.show', compact('report'));
    }

    public function edit($id)
    {
        $report = EvaluationReport::findOrFail($id);
        
        // Seuls les brouillons peuvent être modifiés
        if ($report->status != 'draft') {
            return redirect()->route('evaluation-reports.index')
                            ->with('error', 'Seuls les brouillons peuvent être modifiés');
        }
        
        // Vérifier que c'est le créateur
        if ($report->created_by != Auth::user()->id) {
            abort(403);
        }
        
        return view('evaluation-reports.edit', compact('report'));
    }

    public function update(Request $request, $id)
    {
        $report = EvaluationReport::findOrFail($id);
        
        if ($report->status != 'draft' || $report->created_by != Auth::user()->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'required|string',
            'evaluation_period_start' => 'required|date',
            'evaluation_period_end' => 'required|date|after:evaluation_period_start',
            'recommendations' => 'nullable|string',
        ]);

        // Régénérer les données si les dates ont changé
        $periodStart = Carbon::parse($request->evaluation_period_start);
        $periodEnd = Carbon::parse($request->evaluation_period_end);
        
        if ($periodStart != $report->evaluation_period_start || $periodEnd != $report->evaluation_period_end) {
            $reportData = $this->generateReportData($report->department_id, $periodStart, $periodEnd);
            
            $report->update([
                'title' => $request->title,
                'summary' => $request->summary,
                'evaluation_period_start' => $periodStart,
                'evaluation_period_end' => $periodEnd,
                'attendance_data' => $reportData['attendance'],
                'tasks_data' => $reportData['tasks'],
                'requests_data' => $reportData['requests'],
                'employees_performance' => $reportData['performance'],
                'recommendations' => $request->recommendations,
            ]);
        } else {
            $report->update([
                'title' => $request->title,
                'summary' => $request->summary,
                'recommendations' => $request->recommendations,
            ]);
        }

        return redirect()->route('evaluation-reports.index')
                        ->with('success', 'Rapport d\'évaluation mis à jour avec succès');
    }

    public function send($id)
    {
        $report = EvaluationReport::findOrFail($id);
        
        if ($report->status != 'draft' || $report->created_by != Auth::user()->id) {
            abort(403);
        }

        $report->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return redirect()->route('evaluation-reports.index')
                        ->with('success', 'Rapport envoyé à l\'administration RH avec succès');
    }

    private function generateReportData($departmentId, $startDate, $endDate)
    {
        // Récupérer tous les employés du département
        $employees = User::where('department_id', $departmentId)
                        ->where('role', 'employee')
                        ->get();

        $attendanceData = [];
        $tasksData = [];
        $requestsData = [];
        $performanceData = [];

        foreach ($employees as $employee) {
            // Données de pointage
            $attendance = Attendance::where('user_id', $employee->id)
                                   ->whereBetween('date', [$startDate, $endDate])
                                   ->get();
            
            $attendanceStats = [
                'total_days' => $attendance->count(),
                'present_days' => $attendance->where('status', 'present')->count(),
                'absent_days' => $attendance->where('status', 'absent')->count(),
                'late_days' => $attendance->where('status', 'late')->count(),
                'presence_rate' => $attendance->count() > 0 ? 
                    round(($attendance->where('status', 'present')->count() / $attendance->count()) * 100, 2) : 0,
            ];

            // Données des tâches
            $tasks = Task::where('assigned_to', $employee->id)
                        ->whereBetween('created_at', [$startDate, $endDate])
                        ->get();
            
            $tasksStats = [
                'total_tasks' => $tasks->count(),
                'completed_tasks' => $tasks->where('status', 'completed')->count(),
                'pending_tasks' => $tasks->where('status', 'pending')->count(),
                'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
                'completion_rate' => $tasks->count() > 0 ? 
                    round(($tasks->where('status', 'completed')->count() / $tasks->count()) * 100, 2) : 0,
                'avg_completion_time' => $this->calculateAvgCompletionTime($tasks->where('status', 'completed')),
            ];

            // Données des demandes
            $requests = DepartmentRequest::where('user_id', $employee->id)
                                        ->whereBetween('created_at', [$startDate, $endDate])
                                        ->get();
            
            $requestsStats = [
                'total_requests' => $requests->count(),
                'approved_requests' => $requests->where('status', 'approved')->count(),
                'pending_requests' => $requests->where('status', 'pending')->count(),
                'rejected_requests' => $requests->where('status', 'rejected')->count(),
            ];

            // Calcul de la performance globale
            $performanceScore = $this->calculatePerformanceScore($attendanceStats, $tasksStats, $requestsStats);

            $attendanceData[$employee->id] = $attendanceStats;
            $tasksData[$employee->id] = $tasksStats;
            $requestsData[$employee->id] = $requestsStats;
            $performanceData[$employee->id] = [
                'employee_name' => $employee->name,
                'overall_score' => $performanceScore,
                'attendance_score' => $attendanceStats['presence_rate'],
                'tasks_score' => $tasksStats['completion_rate'],
                'requests_score' => $requests->count() > 0 ? 
                    round(($requests->where('status', 'approved')->count() / $requests->count()) * 100, 2) : 100,
            ];
        }

        return [
            'attendance' => $attendanceData,
            'tasks' => $tasksData,
            'requests' => $requestsData,
            'performance' => $performanceData,
        ];
    }

    private function calculateAvgCompletionTime($completedTasks)
    {
        if ($completedTasks->count() == 0) return 0;
        
        $totalDays = 0;
        foreach ($completedTasks as $task) {
            if ($task->completed_at && $task->created_at) {
                $totalDays += $task->created_at->diffInDays($task->completed_at);
            }
        }
        
        return round($totalDays / $completedTasks->count(), 1);
    }

    private function calculatePerformanceScore($attendance, $tasks, $requests)
    {
        $attendanceWeight = 0.4;
        $tasksWeight = 0.5;
        $requestsWeight = 0.1;

        $attendanceScore = $attendance['presence_rate'];
        $tasksScore = $tasks['completion_rate'];
        $requestsScore = $requests['total_requests'] > 0 ? 
            round(($requests['approved_requests'] / $requests['total_requests']) * 100, 2) : 100;

        return round(
            ($attendanceScore * $attendanceWeight) + 
            ($tasksScore * $tasksWeight) + 
            ($requestsScore * $requestsWeight), 
            2
        );
    }
}