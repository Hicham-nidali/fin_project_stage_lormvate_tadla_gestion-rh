@extends('hr.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Examen du Rapport d'Évaluation</h1>
        <a href="{{ route('hr.evaluation-reports.show', $report->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour au rapport
        </a>
    </div>
    
    <!-- Résumé du rapport -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Résumé du Rapport</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Titre:</strong> {{ $report->title }}<br>
                    <strong>Département:</strong> {{ $report->department->name }}<br>
                    <strong>Chef de département:</strong> {{ $report->creator->name }}
                </div>
                <div class="col-md-6">
                    <strong>Période:</strong> {{ $report->evaluation_period_start->format('d/m/Y') }} - {{ $report->evaluation_period_end->format('d/m/Y') }}<br>
                    <strong>Envoyé le:</strong> {{ $report->sent_at->format('d/m/Y H:i') }}<br>
                    <strong>Employés évalués:</strong> {{ count($report->employees_performance) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Analyse rapide des performances -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Analyse Rapide des Performances</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @php
                    $performances = collect($report->employees_performance);
                    $avgScore = $performances->avg('overall_score');
                    $excellent = $performances->where('overall_score', '>=', 90)->count();
                    $good = $performances->whereBetween('overall_score', [70, 89])->count();
                    $satisfactory = $performances->whereBetween('overall_score', [60, 69])->count();
                    $needsImprovement = $performances->where('overall_score', '<', 60)->count();
                @endphp
                
                <div class="col-md-2">
                    <div class="text-center">
                        <h3 class="text-primary">{{ round($avgScore, 1) }}%</h3>
                        <small>Score moyen</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <h3 class="text-success">{{ $excellent }}</h3>
                        <small>Excellent (≥90%)</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <h3 class="text-info">{{ $good }}</h3>
                        <small>Bon (70-89%)</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <h3 class="text-warning">{{ $satisfactory }}</h3>
                        <small>Satisfaisant (60-69%)</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        <h3 class="text-danger">{{ $needsImprovement }}</h3>
                        <small>À améliorer (<60%)</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-center">
                        @if($avgScore >= 80)
                            <span class="badge bg-success fs-6">Département performant</span>
                        @elseif($avgScore >= 70)
                            <span class="badge bg-primary fs-6">Bonnes performances</span>
                        @elseif($avgScore >= 60)
                            <span class="badge bg-warning fs-6">Performances moyennes</span>
                        @else
                            <span class="badge bg-danger fs-6">Besoin d'accompagnement</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Points d'attention -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Points d'Attention Identifiés</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6 class="text-danger">Employés nécessitant un accompagnement</h6>
                    @if($needsImprovement > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($report->employees_performance as $performance)
                                @if($performance['overall_score'] < 60)
                                    <li class="list-group-item px-0 d-flex justify-content-between">
                                        <span>{{ $performance['employee_name'] }}</span>
                                        <span class="badge bg-danger">{{ $performance['overall_score'] }}%</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <p class="text-success">Aucun employé en difficulté identifié.</p>
                    @endif
                </div>
                
                <div class="col-md-4">
                    <h6 class="text-warning">Problèmes de présence</h6>
                    @php
                        $attendanceIssues = [];
                        foreach($report->attendance_data as $employeeId => $data) {
                            if(($data['presence_rate'] ?? 0) < 90) {
                                $empName = collect($report->employees_performance)->where('employee_name', '!=', null)->first(function($perf, $key) use ($employeeId) {
                                    return $key == $employeeId;
                                })['employee_name'] ?? 'Employé #' . $employeeId;
                                $attendanceIssues[] = ['name' => $empName, 'rate' => $data['presence_rate']];
                            }
                        }
                    @endphp
                    @if(count($attendanceIssues) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($attendanceIssues as $issue)
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span>{{ $issue['name'] }}</span>
                                    <span class="badge bg-warning">{{ $issue['rate'] }}%</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-success">Pas de problème de présence significatif.</p>
                    @endif
                </div>
                
                <div class="col-md-4">
                    <h6 class="text-info">Employés performants</h6>
                    @if($excellent > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($report->employees_performance as $performance)
                                @if($performance['overall_score'] >= 90)
                                    <li class="list-group-item px-0 d-flex justify-content-between">
                                        <span>{{ $performance['employee_name'] }}</span>
                                        <span class="badge bg-success">{{ $performance['overall_score'] }}%</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">Aucun employé avec un score excellent (≥90%).</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé du chef -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Résumé du Chef de Département</h5>
        </div>
        <div class="card-body">
            <p>{{ $report->summary }}</p>
        </div>
    </div>

    <!-- Recommandations du chef -->
    @if($report->recommendations)
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Recommandations du Chef</h5>
        </div>
        <div class="card-body">
            <p>{{ $report->recommendations }}</p>
        </div>
    </div>
    @endif

    <!-- Formulaire d'examen RH -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Examen Administration RH</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('hr.evaluation-reports.store-review', $report->id) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="hr_comments" class="form-label">Commentaires et observations de l'administration RH <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('hr_comments') is-invalid @enderror" id="hr_comments" name="hr_comments" rows="8" required placeholder="Vos commentaires sur ce rapport d'évaluation, vos observations sur les performances du département, et vos recommandations pour l'amélioration...">{{ old('hr_comments') }}</textarea>
                    @error('hr_comments')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Incluez vos observations sur :
                        <ul class="mb-0">
                            <li>La qualité de l'évaluation réalisée par le chef de département</li>
                            <li>L'analyse des performances individuelles et collectives</li>
                            <li>Les actions RH à mettre en place</li>
                            <li>Les recommandations pour le développement des employés</li>
                        </ul>
                    </div>
                </div>

                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Information</h6>
                    <p class="mb-0">Une fois ce rapport examiné, il sera marqué comme "Examiné" et vos commentaires seront visibles par le chef de département. Cette action ne peut pas être annulée.</p>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('hr.evaluation-reports.show', $report->id) }}" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-success" onclick="return confirm('Confirmer l\'examen de ce rapport ?')">
                        <i class="fas fa-check-circle me-2"></i>Marquer comme Examiné
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection