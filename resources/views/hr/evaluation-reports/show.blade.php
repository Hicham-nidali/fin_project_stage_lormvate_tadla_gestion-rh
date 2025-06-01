@extends('hr.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ $report->title }}</h1>
        <div>
            <a href="{{ route('hr.evaluation-reports.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            @if($report->status == 'sent')
                <a href="{{ route('hr.evaluation-reports.review', $report->id) }}" class="btn btn-primary me-2">
                    <i class="fas fa-clipboard-check"></i> Examiner
                </a>
            @endif
            <button type="button" class="btn btn-info" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimer
            </button>
        </div>
    </div>
    
    <!-- Badge de statut RH -->
    @if($report->status == 'sent')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>Ce rapport est en attente d'examen par l'administration RH.
        </div>
    @elseif($report->status == 'reviewed')
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>Ce rapport a été examiné par {{ $report->reviewer->name }} le {{ $report->reviewed_at->format('d/m/Y à H:i') }}.
        </div>
    @endif
    
    <!-- En-tête du rapport -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="mb-1">{{ $report->title }}</h5>
                    <small class="text-muted">Département: {{ $report->department->name }}</small>
                </div>
                <div class="col-md-4 text-end">
                    @if($report->status == 'sent')
                        <span class="badge bg-warning fs-6">En attente d'examen</span>
                    @else
                        <span class="badge bg-success fs-6">Examiné</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Période d'évaluation:</strong><br>
                    {{ $report->evaluation_period_start->format('d/m/Y') }} - {{ $report->evaluation_period_end->format('d/m/Y') }}
                </div>
                <div class="col-md-4">
                    <strong>Chef de département:</strong> {{ $report->creator->name }}<br>
                    <strong>Envoyé le:</strong> {{ $report->sent_at->format('d/m/Y H:i') }}
                </div>
                <div class="col-md-4">
                    @if($report->reviewed_at)
                        <strong>Examiné par:</strong> {{ $report->reviewer->name }}<br>
                        <strong>Examiné le:</strong> {{ $report->reviewed_at->format('d/m/Y H:i') }}
                    @else
                        <span class="text-muted">Pas encore examiné</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé exécutif du chef -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Résumé Exécutif du Chef de Département</h5>
        </div>
        <div class="card-body">
            <p>{{ $report->summary }}</p>
        </div>
    </div>

    <!-- Analyse des performances par employé (Vue RH enrichie) -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Analyse des Performances par Employé</h5>
        </div>
        <div class="card-body">
            @if($report->employees_performance)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Score Global</th>
                            <th>Détail Présence</th>
                            <th>Détail Tâches</th>
                            <th>Détail Demandes</th>
                            <th>Évaluation RH</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report->employees_performance as $employeeId => $performance)
                        @php
                            $attendanceData = $report->attendance_data[$employeeId] ?? [];
                            $tasksData = $report->tasks_data[$employeeId] ?? [];
                            $requestsData = $report->requests_data[$employeeId] ?? [];
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $performance['employee_name'] }}</strong>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress me-2" style="width: 80px; height: 20px;">
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
                                <div class="small">
                                    <div>Présence: {{ $attendanceData['present_days'] ?? 0 }}/{{ $attendanceData['total_days'] ?? 0 }} jours</div>
                                    <div class="text-danger">Absences: {{ $attendanceData['absent_days'] ?? 0 }}</div>
                                    <div class="text-warning">Retards: {{ $attendanceData['late_days'] ?? 0 }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <div>Terminées: {{ $tasksData['completed_tasks'] ?? 0 }}/{{ $tasksData['total_tasks'] ?? 0 }}</div>
                                    <div class="text-primary">En cours: {{ $tasksData['in_progress_tasks'] ?? 0 }}</div>
                                    <div class="text-warning">En attente: {{ $tasksData['pending_tasks'] ?? 0 }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <div class="text-success">Approuvées: {{ $requestsData['approved_requests'] ?? 0 }}</div>
                                    <div class="text-primary">En attente: {{ $requestsData['pending_requests'] ?? 0 }}</div>
                                    <div class="text-danger">Rejetées: {{ $requestsData['rejected_requests'] ?? 0 }}</div>
                                </div>
                            </td>
                            <td>
                                @if($performance['overall_score'] >= 90)
                                    <span class="badge bg-success">Excellent</span>
                                @elseif($performance['overall_score'] >= 80)
                                    <span class="badge bg-primary">Très bien</span>
                                @elseif($performance['overall_score'] >= 70)
                                    <span class="badge bg-info">Bien</span>
                                @elseif($performance['overall_score'] >= 60)
                                    <span class="badge bg-warning">Satisfaisant</span>
                                @else
                                    <span class="badge bg-danger">À améliorer</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <!-- Statistiques globales du département -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Statistiques Globales du Département</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            @php
                                $totalEmployees = count($report->employees_performance);
                                $avgOverallScore = $totalEmployees > 0 ? round(collect($report->employees_performance)->avg('overall_score'), 1) : 0;
                            @endphp
                            <div class="text-center">
                                <h3 class="text-primary">{{ $avgOverallScore }}%</h3>
                                <small>Performance moyenne</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            @php
                                $highPerformers = collect($report->employees_performance)->where('overall_score', '>=', 80)->count();
                            @endphp
                            <div class="text-center">
                                <h3 class="text-success">{{ $highPerformers }}</h3>
                                <small>Employés performants (≥80%)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            @php
                                $lowPerformers = collect($report->employees_performance)->where('overall_score', '<', 60)->count();
                            @endphp
                            <div class="text-center">
                                <h3 class="text-danger">{{ $lowPerformers }}</h3>
                                <small>Employés à accompagner (<60%)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-info">{{ $totalEmployees }}</h3>
                                <small>Total employés évalués</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommandations du chef -->
    @if($report->recommendations)
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Recommandations du Chef de Département</h5>
        </div>
        <div class="card-body">
            <p>{{ $report->recommendations }}</p>
        </div>
    </div>
    @endif

    <!-- Commentaires RH existants -->
    @if($report->hr_comments && $report->status == 'reviewed')
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Commentaires de l'Administration RH</h5>
        </div>
        <div class="card-body">
            <p>{{ $report->hr_comments }}</p>
            <hr>
            <small class="text-muted">
                Examiné par {{ $report->reviewer->name }} le {{ $report->reviewed_at->format('d/m/Y à H:i') }}
            </small>
        </div>
    </div>
    @endif

    <!-- Actions RH -->
    @if($report->status == 'sent')
    <div class="card">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0">Actions Administration RH</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('hr.evaluation-reports.review', $report->id) }}" class="btn btn-primary">
                <i class="fas fa-clipboard-check me-2"></i>Examiner ce rapport
            </a>
        </div>
    </div>
    @endif
</div>
@endsection