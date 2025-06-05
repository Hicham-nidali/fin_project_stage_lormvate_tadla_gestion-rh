<?php
namespace App\Http\Controllers;

use App\Models\Objective;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ObjectiveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\DirectionMiddleware::class);
    }

    public function index(Request $request)
    {
        $query = Objective::with(['department', 'creator']);

        // Filtres
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

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

        $objectives = $query->orderBy('due_date')->paginate(15);
        $departments = Department::all();

        // Statistiques
        $stats = [
            'total' => Objective::count(),
            'active' => Objective::active()->count(),
            'completed' => Objective::completed()->count(),
            'overdue' => Objective::overdue()->count(),
            'critical' => Objective::critical()->active()->count(),
        ];

        // Statistiques par département
        $departmentStats = Department::withCount([
            'objectives',
            'objectives as completed_objectives_count' => function($q) {
                $q->where('status', 'completed');
            },
            'objectives as overdue_objectives_count' => function($q) {
                $q->overdue();
            }
        ])->get();

        // ✅ CORRECTION: Initialiser les alertes
        $alerts = collect();
        
        // Alertes pour objectifs en retard
        $overdueObjectives = Objective::overdue()->with('department')->get();
        foreach ($overdueObjectives as $objective) {
            $alerts->push([
                'type' => 'danger',
                'message' => "⚠️ Objectif en retard: {$objective->title} ({$objective->department->name})"
            ]);
        }
        
        // Alertes pour objectifs critiques
        $criticalObjectives = Objective::critical()->active()->with('department')->get();
        foreach ($criticalObjectives as $objective) {
            $alerts->push([
                'type' => 'warning',
                'message' => "🔥 Objectif critique: {$objective->title} ({$objective->department->name})"
            ]);
        }

        return view('direction.objectives.index', compact(
            'objectives', 
            'departments', 
            'stats', 
            'departmentStats',
            'alerts' // ✅ Ajouter alerts
        ));
    }

    public function create()
    {
        $departments = Department::all();
        $alerts = collect(); // ✅ Initialiser
        
        // Suggestions de dates par défaut
        $defaultStart = now()->startOfMonth();
        $defaultEnd = now()->endOfMonth();

        return view('direction.objectives.create', compact('departments', 'defaultStart', 'defaultEnd', 'alerts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'type' => 'required|in:monthly,quarterly,annual,custom',
            'priority' => 'required|in:low,medium,high,critical',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after:start_date',
            'metrics' => 'nullable|array',
            'notes' => 'nullable|string',
            'is_critical' => 'boolean'
        ]);

        $objective = Objective::create([
            'title' => $request->title,
            'description' => $request->description,
            'department_id' => $request->department_id,
            'type' => $request->type,
            'priority' => $request->priority,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'metrics' => $request->metrics,
            'notes' => $request->notes,
            'is_critical' => $request->boolean('is_critical'),
            'created_by' => Auth::id(),
            'status' => 'assigned'
        ]);

        // Envoyer notification automatique
        $this->sendNotification($objective);

        return redirect()->route('direction.objectives.index')
                        ->with('success', 'Objectif créé et assigné avec succès au département ' . $objective->department->name);
    }

    public function show($id)
    {
        $objective = Objective::with(['department', 'creator'])->findOrFail($id);
        $alerts = collect(); // ✅ Initialiser
        
        // Historique des modifications (si implementé)
        $progressHistory = $this->getProgressHistory($objective);
        
        return view('direction.objectives.show', compact('objective', 'progressHistory', 'alerts'));
    }

    public function edit($id)
    {
        $objective = Objective::findOrFail($id);
        $departments = Department::all();
        $alerts = collect(); // ✅ Initialiser

        return view('direction.objectives.edit', compact('objective', 'departments', 'alerts'));
    }

    public function update(Request $request, $id)
    {
        $objective = Objective::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'type' => 'required|in:monthly,quarterly,annual,custom',
            'priority' => 'required|in:low,medium,high,critical',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after:start_date',
            'metrics' => 'nullable|array',
            'notes' => 'nullable|string',
            'is_critical' => 'boolean'
        ]);

        $oldDepartment = $objective->department_id;
        
        $objective->update([
            'title' => $request->title,
            'description' => $request->description,
            'department_id' => $request->department_id,
            'type' => $request->type,
            'priority' => $request->priority,
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'metrics' => $request->metrics,
            'notes' => $request->notes,
            'is_critical' => $request->boolean('is_critical')
        ]);

        // Si changement de département, renvoyer notification
        if ($oldDepartment != $request->department_id) {
            $this->sendNotification($objective);
        }

        return redirect()->route('direction.objectives.show', $objective->id)
                        ->with('success', 'Objectif modifié avec succès');
    }

    public function destroy($id)
    {
        $objective = Objective::findOrFail($id);
        $departmentName = $objective->department->name;
        
        $objective->delete();

        return redirect()->route('direction.objectives.index')
                        ->with('success', "Objectif supprimé du département {$departmentName}");
    }

    public function cancel($id)
    {
        $objective = Objective::findOrFail($id);
        
        $objective->update([
            'status' => 'cancelled',
            'completion_notes' => 'Objectif annulé par la direction'
        ]);

        return redirect()->back()->with('success', 'Objectif annulé');
    }

    public function dashboard()
    {
        // Vue d'ensemble pour la direction
        $currentMonth = now();
        
        // Statistiques globales
        $totalObjectives = Objective::count();
        $activeObjectives = Objective::active()->count();
        $completedThisMonth = Objective::completed()
                                     ->whereMonth('completed_at', $currentMonth->month)
                                     ->whereYear('completed_at', $currentMonth->year)
                                     ->count();
        $overdueObjectives = Objective::overdue()->count();

        // Objectifs critiques
        $criticalObjectives = Objective::critical()->active()->with(['department'])->get();

        // Performance par département
        $departmentPerformance = Department::withCount([
            'objectives',
            'objectives as completed_count' => function($q) {
                $q->where('status', 'completed');
            },
            'objectives as overdue_count' => function($q) {
                $q->overdue();
            }
        ])->get()->map(function($dept) {
            $total = $dept->objectives_count;
            $completed = $dept->completed_count;
            $overdue = $dept->overdue_count;
            
            return [
                'department' => $dept,
                'total' => $total,
                'completed' => $completed,
                'overdue' => $overdue,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
                'performance_score' => $this->calculateDepartmentScore($dept)
            ];
        });

        // Objectifs récents
        $recentObjectives = Objective::with(['department'])
                                   ->orderBy('created_at', 'desc')
                                   ->take(5)
                                   ->get();

        // Objectifs à échéance proche
        $upcomingDeadlines = Objective::active()
                                    ->where('due_date', '>=', now())
                                    ->where('due_date', '<=', now()->addDays(7))
                                    ->with(['department'])
                                    ->orderBy('due_date')
                                    ->get();

        $alerts = collect(); // ✅ Initialiser

        return view('direction.objectives.dashboard', compact(
            'totalObjectives',
            'activeObjectives', 
            'completedThisMonth',
            'overdueObjectives',
            'criticalObjectives',
            'departmentPerformance',
            'recentObjectives',
            'upcomingDeadlines',
            'alerts' // ✅ Ajouter
        ));
    }

    public function report(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $departmentId = $request->get('department_id');

        $query = Objective::whereBetween('start_date', [$startDate, $endDate]);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $objectives = $query->with(['department', 'creator'])->get();
        $departments = Department::all();

        // Calculs statistiques
        $stats = [
            'total' => $objectives->count(),
            'completed' => $objectives->where('status', 'completed')->count(),
            'in_progress' => $objectives->where('status', 'in_progress')->count(),
            'overdue' => $objectives->where('status', 'overdue')->count(),
            'cancelled' => $objectives->where('status', 'cancelled')->count(),
            'avg_completion_time' => $this->calculateAverageCompletionTime($objectives),
            'completion_rate' => $objectives->count() > 0 ? 
                round(($objectives->where('status', 'completed')->count() / $objectives->count()) * 100, 1) : 0
        ];

        $alerts = collect(); // ✅ Initialiser

        return view('direction.objectives.report', compact(
            'objectives', 
            'departments', 
            'stats', 
            'startDate', 
            'endDate',
            'alerts' // ✅ Ajouter
        ));
    }

    // Méthodes privées
    private function sendNotification($objective)
    {
        // Marquer comme notifié
        $objective->sendNotification();
        
        // Ici vous pouvez ajouter la logique d'envoi d'email, notification push, etc.
        // Par exemple avec le système de notifications de Laravel
        
        // $objective->department->head->notify(new ObjectiveAssigned($objective));
    }

    private function getProgressHistory($objective)
    {
        // Cette méthode pourrait retourner l'historique des modifications
        // si vous implémentez un système de logs
        return [];
    }

    private function calculateDepartmentScore($department)
    {
        $total = $department->objectives_count;
        $completed = $department->completed_count;
        $overdue = $department->overdue_count;
        
        if ($total == 0) return 0;
        
        $completionRate = ($completed / $total) * 100;
        $overdueRate = ($overdue / $total) * 100;
        
        // Score basé sur taux de completion moins pénalité pour retards
        $score = $completionRate - ($overdueRate * 0.5);
        
        return max(0, min(100, round($score, 1)));
    }

    private function calculateAverageCompletionTime($objectives)
    {
        $completed = $objectives->where('status', 'completed')->filter(function($obj) {
            return $obj->completed_at && $obj->start_date;
        });

        if ($completed->count() == 0) return 0;

        $totalDays = $completed->sum(function($obj) {
            return $obj->start_date->diffInDays($obj->completed_at);
        });

        return round($totalDays / $completed->count(), 1);
    }
}