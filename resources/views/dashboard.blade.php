@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-2 mb-4">Tableau de Bord - {{ $department->name }}</h1>
    
    <!-- Statistiques principales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Employés</h6>
                            <h3 class="text-white">{{ $totalEmployees }}</h3>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Tâches en attente</h6>
                            <h3 class="text-white">{{ $pendingTasks }}</h3>
                        </div>
                        <i class="fas fa-tasks fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Demandes en attente</h6>
                            <h3 class="text-white">{{ $pendingRequests }}</h3>
                        </div>
                        <i class="fas fa-paper-plane fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Rapports d'évaluation</h6>
                            @php
                                $evaluationReports = Auth::user()->createdEvaluationReports;
                                $draftReports = $evaluationReports->where('status', 'draft')->count();
                                $sentReports = $evaluationReports->where('status', 'sent')->count();
                            @endphp
                            <h3 class="text-white">{{ $evaluationReports->count() }}</h3>
                            <small class="text-white">{{ $draftReports }} brouillon(s), {{ $sentReports }} envoyé(s)</small>
                        </div>
                        <i class="fas fa-clipboard-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <a href="{{ route('tasks.create') }}" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-plus me-2"></i>Nouvelle Tâche
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('team.index') }}" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-users me-2"></i>Voir l'Équipe
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('attendance.index') }}" class="btn btn-info w-100 mb-2">
                                <i class="fas fa-clock me-2"></i>Présences
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('evaluation-reports.create') }}" class="btn btn-warning w-100 mb-2">
                                <i class="fas fa-clipboard-check me-2"></i>Nouveau Rapport
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('reports.generate.monthly') }}" class="btn btn-secondary w-100 mb-2">
                                <i class="fas fa-chart-line me-2"></i>Rapport Mensuel
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('overtime.index') }}" class="btn btn-dark w-100 mb-2">
                                <i class="fas fa-business-time me-2"></i>Heures Sup.
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertes et notifications importantes -->
    @if($draftReports > 0)
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Attention!</strong> Vous avez {{ $draftReports }} rapport(s) d'évaluation en brouillon. 
        <a href="{{ route('evaluation-reports.index') }}" class="alert-link">Cliquez ici pour les finaliser</a>.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($sentReports > 0)
    <div class="alert alert-info alert-dismissible fade show">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Information:</strong> {{ $sentReports }} rapport(s) d'évaluation ont été envoyés à l'administration RH et sont en cours d'examen.
        <a href="{{ route('evaluation-reports.index') }}" class="alert-link">Voir le statut</a>.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Vue d'ensemble des tâches et activités -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tâches récentes</h5>
                    <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-primary">Voir tout</a>
                </div>
                <div class="card-body">
                    @php
                        $recentTasks = \App\Models\Task::where('department_id', $department->id)
                                                     ->with('assignedTo')
                                                     ->orderBy('created_at', 'desc')
                                                     ->take(5)
                                                     ->get();
                    @endphp
                    
                    @if($recentTasks->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentTasks as $task)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $task->title }}</h6>
                                        <p class="mb-1 text-muted small">Assigné à: {{ $task->assignedTo->name }}</p>
                                        <small class="text-muted">{{ $task->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div>
                                        @if($task->status == 'pending')
                                            <span class="badge bg-secondary">En attente</span>
                                        @elseif($task->status == 'in_progress')
                                            <span class="badge bg-primary">En cours</span>
                                        @elseif($task->status == 'completed')
                                            <span class="badge bg-success">Terminé</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">Aucune tâche récente.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Rapports d'évaluation</h5>
                    <a href="{{ route('evaluation-reports.index') }}" class="btn btn-sm btn-warning">Gérer</a>
                </div>
                <div class="card-body">
                    @php
                        $recentEvaluationReports = Auth::user()->createdEvaluationReports()
                                                             ->orderBy('created_at', 'desc')
                                                             ->take(4)
                                                             ->get();
                    @endphp
                    
                    @if($recentEvaluationReports->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentEvaluationReports as $report)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $report->title }}</h6>
                                        <p class="mb-1 text-muted small">
                                            {{ $report->evaluation_period_start->format('d/m/Y') }} - {{ $report->evaluation_period_end->format('d/m/Y') }}
                                        </p>
                                        <small class="text-muted">{{ $report->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div>
                                        @if($report->status == 'draft')
                                            <span class="badge bg-secondary">Brouillon</span>
                                        @elseif($report->status == 'sent')
                                            <span class="badge bg-primary">Envoyé</span>
                                        @else
                                            <span class="badge bg-success">Examiné</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Bouton pour créer un nouveau rapport -->
                        <div class="mt-3">
                            <a href="{{ route('evaluation-reports.create') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-plus me-2"></i>Créer un nouveau rapport d'évaluation
                            </a>
                        </div>
                    @else
                        <div class="text-center">
                            <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun rapport d'évaluation créé.</p>
                            <a href="{{ route('evaluation-reports.create') }}" class="btn btn-warning">
                                <i class="fas fa-plus me-2"></i>Créer le premier rapport
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Performance de l'équipe -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Performance de l'équipe</h5>
                </div>
                <div class="card-body">
                    @php
                        $teamMembers = \App\Models\User::where('department_id', $department->id)
                                                      ->where('role', 'employee')
                                                      ->withCount([
                                                          'tasks as completed_tasks' => function($q) {
                                                              $q->where('status', 'completed');
                                                          },
                                                          'tasks as pending_tasks' => function($q) {
                                                              $q->where('status', 'pending');
                                                          }
                                                      ])
                                                      ->get();
                    @endphp
                    
                    @if($teamMembers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Employé</th>
                                        <th>Tâches terminées</th>
                                        <th>Tâches en attente</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($teamMembers as $member)
                                    @php
                                        $totalTasks = $member->completed_tasks + $member->pending_tasks;
                                        $completionRate = $totalTasks > 0 ? round(($member->completed_tasks / $totalTasks) * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $member->name }}</td>
                                        <td><span class="badge bg-success">{{ $member->completed_tasks }}</span></td>
                                        <td><span class="badge bg-warning">{{ $member->pending_tasks }}</span></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar 
                                                    @if($completionRate >= 80) bg-success 
                                                    @elseif($completionRate >= 60) bg-warning 
                                                    @else bg-danger @endif" 
                                                    role="progressbar" 
                                                    style="width: {{ $completionRate }}%">
                                                    {{ $completionRate }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Aucun employé dans ce département.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Statistiques rapides</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Taux de présence moyen</span>
                            <strong class="text-success">92%</strong>
                        </div>
                        <div class="progress mt-1">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 92%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Complétion des tâches</span>
                            <strong class="text-primary">87%</strong>
                        </div>
                        <div class="progress mt-1">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 87%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Satisfaction générale</span>
                            <strong class="text-info">8.5/10</strong>
                        </div>
                        <div class="progress mt-1">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 85%"></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-center">
                        <a href="{{ route('evaluation-reports.create') }}" class="btn btn-warning btn-sm w-100">
                            <i class="fas fa-chart-bar me-2"></i>Générer rapport d'évaluation
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection