@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-chart-line me-3"></i>Tableau de Bord - Mes Objectifs</h1>
        <div>
            <a href="{{ route('objectives.index') }}" class="btn btn-primary">
                <i class="fas fa-list me-2"></i>Voir tous mes objectifs
            </a>
        </div>
    </div>

    <!-- Alertes importantes -->
    @if(count($alerts) > 0)
        @foreach($alerts as $alert)
        <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show">
            <strong><i class="fas fa-exclamation-triangle me-2"></i>Attention !</strong>
            {{ $alert['message'] }}
            <a href="{{ $alert['action'] }}" class="alert-link ms-2">Voir maintenant</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endforeach
    @endif

    <!-- Statistiques principales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total Objectifs</h6>
                            <h2 class="text-white">{{ $totalObjectives }}</h2>
                        </div>
                        <i class="fas fa-bullseye fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Actifs</h6>
                            <h2 class="text-white">{{ $activeObjectives }}</h2>
                        </div>
                        <i class="fas fa-play fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Terminés ce mois</h6>
                            <h2 class="text-white">{{ $completedThisMonth }}</h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">En retard</h6>
                            <h2 class="text-white">{{ $overdueObjectives }}</h2>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Progression globale -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-light text-center">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Progression Globale</h5>
                </div>
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <canvas id="progressChart" width="150" height="150"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <h3 class="mb-0">{{ $avgProgress }}%</h3>
                            <small class="text-muted">Moyenne</small>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar 
                                @if($avgProgress >= 80) bg-success 
                                @elseif($avgProgress >= 60) bg-warning 
                                @else bg-danger @endif" 
                                style="width: {{ $avgProgress }}%">
                                {{ $avgProgress }}%
                            </div>
                        </div>
                        <small class="text-muted">Progression moyenne de tous vos objectifs</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Objectifs par priorité -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>Par Priorité</h5>
                </div>
                <div class="card-body">
                    @foreach($objectivesByPriority as $priority => $count)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            @switch($priority)
                                @case('critical')
                                    <span class="badge bg-dark me-2">Critique</span>
                                    @break
                                @case('high')
                                    <span class="badge bg-danger me-2">Haute</span>
                                    @break
                                @case('medium')
                                    <span class="badge bg-warning me-2">Moyenne</span>
                                    @break
                                @case('low')
                                    <span class="badge bg-success me-2">Faible</span>
                                    @break
                            @endswitch
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $count }}</h5>
                        </div>
                    </div>
                    @endforeach
                    
                    @if($objectivesByPriority['critical'] > 0)
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-fire me-2"></i>
                        <strong>{{ $objectivesByPriority['critical'] }} objectif(s) critique(s)</strong> nécessitent votre attention prioritaire.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Performance mensuelle -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Performance Mensuelle</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Objectifs récents et actions rapides -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Objectifs Récents</h5>
                    <a href="{{ route('objectives.index') }}" class="btn btn-sm btn-primary">Voir tout</a>
                </div>
                <div class="card-body">
                    @if($recentObjectives->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Objectif</th>
                                        <th>Priorité</th>
                                        <th>Échéance</th>
                                        <th>Progression</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentObjectives as $objective)
                                    <tr class="{{ $objective->is_overdue ? 'table-warning' : '' }}">
                                        <td>
                                            <strong>{{ $objective->title }}</strong>
                                            @if($objective->is_critical)
                                                <i class="fas fa-fire text-danger ms-2"></i>
                                            @endif
                                            <br><small class="text-muted">{{ Str::limit($objective->description, 50) }}</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $objective->priority_badge['class'] }}">
                                                {{ $objective->priority_badge['text'] }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $objective->due_date->format('d/m/Y') }}
                                            @if($objective->is_overdue)
                                                <br><small class="text-danger">{{ abs($objective->days_remaining) }}j de retard</small>
                                            @elseif($objective->days_until_due <= 3)
                                                <br><small class="text-warning">{{ $objective->days_until_due }}j restant(s)</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar {{ $objective->progress_bar_color }}" 
                                                     style="width: {{ $objective->progress_percentage }}%">
                                                    {{ $objective->progress_percentage }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('objectives.show', $objective->id) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun objectif récent.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Actions rapides -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions Rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('objectives.index', ['status' => 'assigned']) }}" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>Nouveaux objectifs ({{ $objectivesByPriority['critical'] + $objectivesByPriority['high'] }})
                        </a>
                        
                        <a href="{{ route('objectives.index', ['status' => 'overdue']) }}" 
                           class="btn btn-outline-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>Objectifs en retard ({{ $overdueObjectives }})
                        </a>
                        
                        <a href="{{ route('objectives.index', ['priority' => 'critical']) }}" 
                           class="btn btn-outline-warning">
                            <i class="fas fa-fire me-2"></i>Objectifs critiques ({{ $objectivesByPriority['critical'] }})
                        </a>
                        
                        <hr>
                        
                        <a href="{{ route('objectives.history') }}" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-history me-2"></i>Historique complet
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistiques de performance -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Performance</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @php
                            $completionRate = $totalObjectives > 0 ? round(($completedThisMonth / $totalObjectives) * 100, 1) : 0;
                        @endphp
                        <h3 class="
                            @if($completionRate >= 80) text-success 
                            @elseif($completionRate >= 60) text-warning 
                            @else text-danger @endif">
                            {{ $completionRate }}%
                        </h3>
                        <p class="text-muted">Taux de completion ce mois</p>
                    </div>
                    
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar 
                            @if($completionRate >= 80) bg-success 
                            @elseif($completionRate >= 60) bg-warning 
                            @else bg-danger @endif" 
                            style="width: {{ $completionRate }}%">
                            {{ $completionRate }}%
                        </div>
                    </div>
                    
                    <div class="text-center">
                        @if($completionRate >= 80)
                            <div class="alert alert-success">
                                <i class="fas fa-trophy me-2"></i>Excellente performance !
                            </div>
                        @elseif($completionRate >= 60)
                            <div class="alert alert-warning">
                                <i class="fas fa-thumbs-up me-2"></i>Performance correcte
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-chart-line me-2"></i>Marge d'amélioration
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique circulaire de progression
    const progressCtx = document.getElementById('progressChart').getContext('2d');
    new Chart(progressCtx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [{{ $avgProgress }}, {{ 100 - $avgProgress }}],
                backgroundColor: [
                    @if($avgProgress >= 80) '#28a745'
                    @elseif($avgProgress >= 60) '#ffc107'
                    @else '#dc3545' @endif,
                    '#e9ecef'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                }
            }
        }
    });

    // Graphique de performance mensuelle
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: [@foreach($monthlyPerformance as $month) "{{ $month['month'] }}", @endforeach],
            datasets: [{
                label: 'Taux de completion (%)',
                data: [@foreach($monthlyPerformance as $month) {{ $month['completion_rate'] }}, @endforeach],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endsection