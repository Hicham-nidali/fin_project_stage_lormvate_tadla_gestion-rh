@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-clipboard-check me-3"></i>{{ $report->title }}</h1>
        <div>
            <a href="{{ route('direction.reports.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button type="button" class="btn btn-info" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimer
            </button>
        </div>
    </div>
    
    <!-- En-tête du rapport -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-white">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="mb-1 text-white">{{ $report->title }}</h5>
                    <small class="text-white-50">Rapport d'Évaluation Départemental</small>
                </div>
                <div class="col-md-4 text-end">
                    @if($report->status == 'draft')
                        <span class="badge bg-light text-secondary fs-6">Brouillon</span>
                    @elseif($report->status == 'sent')
                        <span class="badge bg-light text-primary fs-6">Envoyé</span>
                    @else
                        <span class="badge bg-light text-success fs-6">Examiné</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Département:</strong><br>
                    <span class="badge bg-primary">{{ $report->department->name }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Créé par:</strong> {{ $report->creator->name }}<br>
                    <strong>Date de création:</strong> {{ $report->created_at->format('d/m/Y H:i') }}
                </div>
            </div>
            
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <strong>Période d'évaluation:</strong><br>
                    {{ $report->evaluation_period_start->format('d/m/Y') }} - {{ $report->evaluation_period_end->format('d/m/Y') }}
                </div>
                @if($report->sent_at)
                <div class="col-md-6">
                    <strong>Envoyé le:</strong> {{ $report->sent_at->format('d/m/Y H:i') }}
                    @if($report->reviewed_at)
                        <br><strong>Examiné le:</strong> {{ $report->reviewed_at->format('d/m/Y H:i') }}
                        <br><strong>Examiné par:</strong> {{ $report->reviewer->name }}
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Résumé exécutif -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-summary me-2"></i>Résumé Exécutif</h5>
        </div>
        <div class="card-body">
            <p class="lead">{!! nl2br(e($report->summary)) !!}</p>
        </div>
    </div>

    <!-- Performance globale par employé -->
    @if($report->employees_performance)
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Performance Globale des Employés</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Employé</th>
                            <th>Score Global</th>
                            <th>Présence</th>
                            <th>Tâches</th>
                            <th>Demandes</th>
                            <th>Niveau</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report->employees_performance as $employeeId => $performance)
                        <tr>
                            <td><strong>{{ $performance['employee_name'] }}</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress me-2" style="width: 100px; height: 20px;">
                                        <div class="progress-bar 
                                            @if($performance['overall_score'] >= 80) bg-success
                                            @elseif($performance['overall_score'] >= 60) bg-warning  
                                            @else bg-danger @endif" 
                                            role="progressbar" 
                                            style="width: {{ $performance['overall_score'] }}%">
                                        </div>
                                    </div>
                                    <span class="fw-bold">{{ $performance['overall_score'] }}%</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $performance['attendance_score'] >= 90 ? 'success' : ($performance['attendance_score'] >= 80 ? 'warning' : 'danger') }}">
                                    {{ $performance['attendance_score'] }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $performance['tasks_score'] >= 90 ? 'success' : ($performance['tasks_score'] >= 80 ? 'warning' : 'danger') }}">
                                    {{ $performance['tasks_score'] }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $performance['requests_score'] >= 90 ? 'success' : ($performance['requests_score'] >= 80 ? 'warning' : 'danger') }}">
                                    {{ $performance['requests_score'] }}%
                                </span>
                            </td>
                            <td>
                                @if($performance['overall_score'] >= 85)
                                    <span class="badge bg-success">Excellent</span>
                                @elseif($performance['overall_score'] >= 70)
                                    <span class="badge bg-primary">Bon</span>
                                @elseif($performance['overall_score'] >= 60)
                                    <span class="badge bg-warning">Moyen</span>
                                @else
                                    <span class="badge bg-danger">À améliorer</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Détails par section -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Données de Pointage</h6>
                </div>
                <div class="card-body">
                    @if($report->attendance_data)
                        @php
                            $totalPresent = collect($report->attendance_data)->sum('present_days');
                            $totalDays = collect($report->attendance_data)->sum('total_days');
                            $avgPresenceRate = $totalDays > 0 ? round(($totalPresent / $totalDays) * 100, 1) : 0;
                        @endphp
                        <div class="text-center mb-3">
                            <h3 class="text-primary">{{ $avgPresenceRate }}%</h3>
                            <small class="text-muted">Taux de présence moyen</small>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Jours travaillés:</span>
                                <strong class="text-success">{{ $totalPresent }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Total jours:</span>
                                <strong>{{ $totalDays }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Absences:</span>
                                <strong class="text-danger">{{ collect($report->attendance_data)->sum('absent_days') }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Retards:</span>
                                <strong class="text-warning">{{ collect($report->attendance_data)->sum('late_days') }}</strong>
                            </li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>Données des Tâches</h6>
                </div>
                <div class="card-body">
                    @if($report->tasks_data)
                        @php
                            $totalTasks = collect($report->tasks_data)->sum('total_tasks');
                            $completedTasks = collect($report->tasks_data)->sum('completed_tasks');
                            $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
                        @endphp
                        <div class="text-center mb-3">
                            <h3 class="text-success">{{ $completionRate }}%</h3>
                            <small class="text-muted">Taux de complétion</small>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Tâches terminées:</span>
                                <strong class="text-success">{{ $completedTasks }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Total tâches:</span>
                                <strong>{{ $totalTasks }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>En cours:</span>
                                <strong class="text-primary">{{ collect($report->tasks_data)->sum('in_progress_tasks') }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>En attente:</span>
                                <strong class="text-warning">{{ collect($report->tasks_data)->sum('pending_tasks') }}</strong>
                            </li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Données des Demandes</h6>
                </div>
                <div class="card-body">
                    @if($report->requests_data)
                        @php
                            $totalRequests = collect($report->requests_data)->sum('total_requests');
                            $approvedRequests = collect($report->requests_data)->sum('approved_requests');
                            $approvalRate = $totalRequests > 0 ? round(($approvedRequests / $totalRequests) * 100, 1) : 0;
                        @endphp
                        <div class="text-center mb-3">
                            <h3 class="text-warning">{{ $approvalRate }}%</h3>
                            <small class="text-muted">Taux d'approbation</small>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Demandes approuvées:</span>
                                <strong class="text-success">{{ $approvedRequests }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Total demandes:</span>
                                <strong>{{ $totalRequests }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>En attente:</span>
                                <strong class="text-primary">{{ collect($report->requests_data)->sum('pending_requests') }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span>Rejetées:</span>
                                <strong class="text-danger">{{ collect($report->requests_data)->sum('rejected_requests') }}</strong>
                            </li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recommandations -->
    @if($report->recommendations)
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Recommandations</h5>
        </div>
        <div class="card-body">
            <p>{!! nl2br(e($report->recommendations)) !!}</p>
        </div>
    </div>
    @endif

    <!-- Commentaires RH -->
    @if($report->hr_comments && $report->status == 'reviewed')
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Commentaires de l'Administration RH</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <p class="mb-2">{!! nl2br(e($report->hr_comments)) !!}</p>
                <hr>
                <small class="text-muted">
                    <i class="fas fa-user me-1"></i>Examiné par <strong>{{ $report->reviewer->name }}</strong> 
                    le {{ $report->reviewed_at->format('d/m/Y à H:i') }}
                </small>
            </div>
        </div>
    </div>
    @endif

    <!-- Métadonnées -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations Complémentaires</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Détails du Rapport</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>ID du rapport:</span>
                            <strong>#{{ $report->id }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Type:</span>
                            <strong>Rapport d'Évaluation</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Statut:</span>
                            <strong>{{ ucfirst($report->status) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Dernière modification:</span>
                            <strong>{{ $report->updated_at->format('d/m/Y H:i') }}</strong>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Workflow d'Approbation</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Créé par:</span>
                            <strong>{{ $report->creator->name }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Département:</span>
                            <strong>{{ $report->department->name }}</strong>
                        </li>
                        @if($report->sent_at)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Envoyé le:</span>
                            <strong>{{ $report->sent_at->format('d/m/Y H:i') }}</strong>
                        </li>
                        @endif
                        @if($report->reviewer)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Examiné par:</span>
                            <strong>{{ $report->reviewer->name }}</strong>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Améliorer l'impression
window.addEventListener('beforeprint', function() {
    document.title = '{{ $report->title }} - {{ $report->department->name }} - Évaluation';
});
</script>
@endsection

<style>
@media print {
    .btn, .card-header, .no-print {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
        margin-bottom: 15px !important;
    }
    
    .badge {
        border: 1px solid #000 !important;
    }
    
    .progress {
        border: 1px solid #000 !important;
    }
}
</style>