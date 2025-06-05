@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-chart-line me-3"></i>Rapport des Objectifs</h1>
        <div>
            <button onclick="window.print()" class="btn btn-secondary me-2">
                <i class="fas fa-print me-2"></i>Imprimer
            </button>
            <a href="{{ route('direction.objectives.index') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Filtres du rapport</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('direction.objectives.report') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Date début</label>
                    <input type="date" class="form-control" name="start_date" 
                           value="{{ $startDate }}" id="start_date">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Date fin</label>
                    <input type="date" class="form-control" name="end_date" 
                           value="{{ $endDate }}" id="end_date">
                </div>
                <div class="col-md-3">
                    <label for="department_id" class="form-label">Département</label>
                    <select name="department_id" class="form-select" id="department_id">
                        <option value="">Tous les départements</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" 
                                {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block w-100">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques générales -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <h3 class="text-white">{{ $stats['total'] }}</h3>
                    <p class="mb-0">Total Objectifs</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <h3 class="text-white">{{ $stats['completed'] }}</h3>
                    <p class="mb-0">Terminés</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body text-center">
                    <h3 class="text-white">{{ $stats['in_progress'] }}</h3>
                    <p class="mb-0">En cours</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body text-center">
                    <h3 class="text-white">{{ $stats['overdue'] }}</h3>
                    <p class="mb-0">En retard</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body text-center">
                    <h3 class="text-white">{{ $stats['cancelled'] }}</h3>
                    <p class="mb-0">Annulés</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <h3 class="text-white">{{ $stats['completion_rate'] }}%</h3>
                    <p class="mb-0">Taux de completion</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Répartition par statut</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Performance par département</h5>
                </div>
                <div class="card-body">
                    <canvas id="departmentChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Métriques détaillées -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Métriques de performance</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center p-3 border rounded">
                        <h4 class="text-primary">{{ $stats['avg_completion_time'] }} jours</h4>
                        <p class="mb-0">Temps moyen de completion</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 border rounded">
                        <h4 class="text-success">{{ $stats['completion_rate'] }}%</h4>
                        <p class="mb-0">Taux de réussite global</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 border rounded">
                        @php
                            $avgProgress = $objectives->avg('progress_percentage');
                        @endphp
                        <h4 class="text-info">{{ round($avgProgress, 1) }}%</h4>
                        <p class="mb-0">Progression moyenne</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste détaillée -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Liste détaillée des objectifs</h5>
        </div>
        <div class="card-body">
            @if($objectives->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Objectif</th>
                                <th>Département</th>
                                <th>Type</th>
                                <th>Priorité</th>
                                <th>Période</th>
                                <th>Progression</th>
                                <th>Statut</th>
                                <th>Temps completion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($objectives as $objective)
                            <tr>
                                <td>
                                    <strong>{{ $objective->title }}</strong>
                                    @if($objective->is_critical)
                                        <i class="fas fa-fire text-danger ms-1"></i>
                                    @endif
                                </td>
                                <td>{{ $objective->department->name }}</td>
                                <td>
                                    <span class="badge 
                                        @if($objective->type == 'monthly') bg-info
                                        @elseif($objective->type == 'quarterly') bg-primary  
                                        @elseif($objective->type == 'annual') bg-success
                                        @else bg-secondary @endif">
                                        {{ ucfirst($objective->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $objective->priority_badge['class'] }}">
                                        {{ $objective->priority_badge['text'] }}
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        {{ $objective->start_date->format('d/m/Y') }} - 
                                        {{ $objective->due_date->format('d/m/Y') }}
                                    </small>
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
                                    <span class="badge {{ $objective->status_badge['class'] }}">
                                        {{ $objective->status_badge['text'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($objective->completed_at)
                                        {{ $objective->start_date->diffInDays($objective->completed_at) }} jours
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun objectif trouvé pour la période sélectionnée.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique par statut
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Terminés', 'En cours', 'En retard', 'Annulés'],
            datasets: [{
                data: [
                    {{ $stats['completed'] }}, 
                    {{ $stats['in_progress'] }}, 
                    {{ $stats['overdue'] }}, 
                    {{ $stats['cancelled'] }}
                ],
                backgroundColor: [
                    '#28a745', '#ffc107', '#dc3545', '#6c757d'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Graphique par département
    const departmentCtx = document.getElementById('departmentChart').getContext('2d');
    @php
        $deptStats = $objectives->groupBy('department.name')->map(function($items, $key) {
            return [
                'name' => $key,
                'total' => $items->count(),
                'completed' => $items->where('status', 'completed')->count()
            ];
        });
    @endphp
    
    new Chart(departmentCtx, {
        type: 'bar',
        data: {
            labels: [@foreach($deptStats as $dept) "{{ $dept['name'] }}", @endforeach],
            datasets: [{
                label: 'Total',
                data: [@foreach($deptStats as $dept) {{ $dept['total'] }}, @endforeach],
                backgroundColor: 'rgba(54, 162, 235, 0.8)'
            }, {
                label: 'Terminés',
                data: [@foreach($deptStats as $dept) {{ $dept['completed'] }}, @endforeach],
                backgroundColor: 'rgba(40, 167, 69, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endsection