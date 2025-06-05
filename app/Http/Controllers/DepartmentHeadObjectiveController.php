<?php
namespace App\Http\Controllers;

use App\Models\Objective;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DepartmentHeadObjectiveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\DepartmentHeadMiddleware::class);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $departmentId = $user->department_id;

        if (!$departmentId) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }

        $query = Objective::forDepartment($departmentId)->with(['creator']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('month')) {
            $month = $request->month;
            $year = $request->get('year', now()->year);
            $query->whereMonth('start_date', $month)->whereYear('start_date', $year);
        }

        $objectives = $query->orderBy('due_date')->paginate(10);

        // Statistiques du département
        $stats = [
            'total' => Objective::forDepartment($departmentId)->count(),
            'assigned' => Objective::forDepartment($departmentId)->where('status', 'assigned')->count(),
            'in_progress' => Objective::forDepartment($departmentId)->where('status', 'in_progress')->count(),
            'completed' => Objective::forDepartment($departmentId)->where('status', 'completed')->count(),
            'overdue' => Objective::forDepartment($departmentId)->overdue()->count(),
            'critical' => Objective::forDepartment($departmentId)->critical()->active()->count(),
        ];

        // Calcul du taux de completion
        $completionRate = $stats['total'] > 0 ? 
            round(($stats['completed'] / $stats['total']) * 100, 1) : 0;

        // Objectifs critiques
        $criticalObjectives = Objective::forDepartment($departmentId)
                                     ->critical()
                                     ->active()
                                     ->orderBy('due_date')
                                     ->get();

        // Objectifs à échéance proche (7 jours)
        $upcomingDeadlines = Objective::forDepartment($departmentId)
                                    ->active()
                                    ->where('due_date', '>=', now())
                                    ->where('due_date', '<=', now()->addDays(7))
                                    ->orderBy('due_date')
                                    ->get();

        // Nouveaux objectifs non lus (assignés récemment)
        $newObjectives = Objective::forDepartment($departmentId)
                                ->where('status', 'assigned')
                                ->where('created_at', '>=', now()->subDays(7))
                                ->orderBy('created_at', 'desc')
                                ->get();

        return view('objectives.index', compact(
            'objectives',
            'stats',
            'completionRate',
            'criticalObjectives',
            'upcomingDeadlines',
            'newObjectives'
        ));
    }

    public function show($id)
    {
        $user = Auth::user();
        $objective = Objective::forDepartment($user->department_id)->findOrFail($id);

        // Marquer comme vu si c'était assigné
        if ($objective->status === 'assigned') {
            $objective->update(['status' => 'in_progress']);
        }

        return view('objectives.show', compact('objective'));
    }

    public function updateProgress(Request $request, $id)
    {
        $user = Auth::user();
        $objective = Objective::forDepartment($user->department_id)->findOrFail($id);

        $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);

        $objective->updateProgress(
            $request->progress_percentage,
            $request->notes
        );

        $message = $objective->status === 'completed' ? 
            'Objectif marqué comme terminé avec succès!' :
            'Progression de l\'objectif mise à jour avec succès!';

        return redirect()->back()->with('success', $message);
    }

    public function complete(Request $request, $id)
    {
        $user = Auth::user();
        $objective = Objective::forDepartment($user->department_id)->findOrFail($id);

        $request->validate([
            'completion_notes' => 'nullable|string|max:1000'
        ]);

        $objective->markAsCompleted($request->completion_notes);

        return redirect()->route('objectives.index')
                        ->with('success', 'Objectif marqué comme terminé avec succès!');
    }

    public function reopen($id)
    {
        $user = Auth::user();
        $objective = Objective::forDepartment($user->department_id)->findOrFail($id);

        if (!in_array($objective->status, ['completed', 'cancelled'])) {
            return redirect()->back()->with('error', 'Cet objectif ne peut pas être réouvert.');
        }

        $objective->update([
            'status' => 'in_progress',
            'completed_at' => null,
            'completion_notes' => null
        ]);

        return redirect()->back()->with('success', 'Objectif réouvert avec succès!');
    }

    public function dashboard()
    {
        $user = Auth::user();
        $departmentId = $user->department_id;

        if (!$departmentId) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }

        // Vue d'ensemble des objectifs du département
        $currentMonth = now();
        
        // Statistiques rapides
        $totalObjectives = Objective::forDepartment($departmentId)->count();
        $activeObjectives = Objective::forDepartment($departmentId)->active()->count();
        $completedThisMonth = Objective::forDepartment($departmentId)
                                     ->completed()
                                     ->whereMonth('completed_at', $currentMonth->month)
                                     ->whereYear('completed_at', $currentMonth->year)
                                     ->count();
        $overdueObjectives = Objective::forDepartment($departmentId)->overdue()->count();

        // Objectifs par priorité
        $objectivesByPriority = [
            'critical' => Objective::forDepartment($departmentId)->where('priority', 'critical')->active()->count(),
            'high' => Objective::forDepartment($departmentId)->where('priority', 'high')->active()->count(),
            'medium' => Objective::forDepartment($departmentId)->where('priority', 'medium')->active()->count(),
            'low' => Objective::forDepartment($departmentId)->where('priority', 'low')->active()->count(),
        ];

        // Progression globale du département
        $allObjectives = Objective::forDepartment($departmentId)->get();
        $avgProgress = $allObjectives->count() > 0 ? 
            round($allObjectives->avg('progress_percentage'), 1) : 0;

        // Objectifs récents assignés
        $recentObjectives = Objective::forDepartment($departmentId)
                                   ->where('created_at', '>=', now()->subDays(30))
                                   ->orderBy('created_at', 'desc')
                                   ->take(5)
                                   ->get();

        // Performance mensuelle (3 derniers mois)
        $monthlyPerformance = [];
        for ($i = 2; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthObjectives = Objective::forDepartment($departmentId)
                                      ->whereMonth('start_date', $month->month)
                                      ->whereYear('start_date', $month->year)
                                      ->get();
            
            $monthlyPerformance[] = [
                'month' => $month->format('M Y'),
                'total' => $monthObjectives->count(),
                'completed' => $monthObjectives->where('status', 'completed')->count(),
                'completion_rate' => $monthObjectives->count() > 0 ? 
                    round(($monthObjectives->where('status', 'completed')->count() / $monthObjectives->count()) * 100, 1) : 0
            ];
        }

        // Alertes importantes
        $alerts = [];
        
        if ($overdueObjectives > 0) {
            $alerts[] = [
                'type' => 'danger',
                'message' => "Vous avez {$overdueObjectives} objectif(s) en retard",
                'action' => route('objectives.index', ['status' => 'overdue'])
            ];
        }

        $criticalCount = $objectivesByPriority['critical'];
        if ($criticalCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Vous avez {$criticalCount} objectif(s) critique(s) en cours",
                'action' => route('objectives.index', ['priority' => 'critical'])
            ];
        }

        $upcomingCount = Objective::forDepartment($departmentId)
                                ->active()
                                ->where('due_date', '>=', now())
                                ->where('due_date', '<=', now()->addDays(3))
                                ->count();
        
        if ($upcomingCount > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "Vous avez {$upcomingCount} objectif(s) à échéance dans les 3 prochains jours",
                'action' => route('objectives.index')
            ];
        }

        return view('objectives.dashboard', compact(
            'totalObjectives',
            'activeObjectives',
            'completedThisMonth',
            'overdueObjectives',
            'objectivesByPriority',
            'avgProgress',
            'recentObjectives',
            'monthlyPerformance',
            'alerts'
        ));
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        $departmentId = $user->department_id;

        $startDate = $request->get('start_date', now()->subMonths(3));
        $endDate = $request->get('end_date', now());

        $objectives = Objective::forDepartment($departmentId)
                              ->whereBetween('start_date', [$startDate, $endDate])
                              ->orderBy('created_at', 'desc')
                              ->paginate(15);

        // Statistiques de la période
        $periodStats = [
            'total' => $objectives->total(),
            'completed' => Objective::forDepartment($departmentId)
                                  ->whereBetween('start_date', [$startDate, $endDate])
                                  ->where('status', 'completed')
                                  ->count(),
            'avg_completion_time' => $this->calculateAverageCompletionTime($departmentId, $startDate, $endDate),
        ];

        return view('objectives.history', compact(
            'objectives',
            'periodStats',
            'startDate',
            'endDate'
        ));
    }

    // Méthodes privées
    private function calculateAverageCompletionTime($departmentId, $startDate, $endDate)
    {
        $completed = Objective::forDepartment($departmentId)
                             ->whereBetween('start_date', [$startDate, $endDate])
                             ->where('status', 'completed')
                             ->whereNotNull('completed_at')
                             ->get();

        if ($completed->count() == 0) return 0;

        $totalDays = $completed->sum(function($obj) {
            return $obj->start_date->diffInDays($obj->completed_at);
        });

        return round($totalDays / $completed->count(), 1);
    }
}