@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-chart-bar me-3"></i>Tableau de Bord - Rapports</h1>
        <a href="{{ route('direction.reports.index') }}" class="btn btn-primary">
            <i class="fas fa-list me-2"></i>Voir Tous les Rapports
        </a>
    </div>

    <!-- Statistiques principales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total Rapports</h6>
                            <h3 class="text-white">{{ $stats['total_reports'] }}</h3>
                            <small class="text-white-50">Tous types confondus</small>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Départementaux</h6>
                            <h3 class="text-white">{{ $stats['department_reports'] }}</h3>
                            <small class="text-white-50">Rapports de chefs</small>
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
                            <h6 class="text-white">Évaluations</h6>
                            <h3 class="text-white">{{ $stats['evaluation_reports'] }}</h3>
                            <small class="text-white-50">Rapports d'évaluation</small>
                        </div>
                        <i class="fas fa-clipboard-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Ce Mois</h6>
                            <h3 class="text-white">{{ $stats['reports_this_month'] }}</h3>
                            <small class="text-white-50">Nouveaux rapports</small>
                        </div>
                        <i class="fas fa-calendar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques détaillées -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Statuts des Rapports</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Rapports par Département</h5>
                </div>
                <div class="card-body">
                    <canvas id="departmentChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>Évolution Mensuelle</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendsChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Rapports par département -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Activité par Département</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Département</th>
                                    <th>Départementaux</th>
                                    <th>Évaluations</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportsByDepartment as $dept)
                                <tr>
                                    <td><strong>{{ $dept->name }}</strong></td>
                                    <td>
                                        <span class="badge bg-success">{{ $dept->department_reports_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ $dept->evaluation_reports_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $dept->total_reports }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Rapports Récents</h5>
                </div>
                <div class="card-body">
                    @foreach($recentReports->take(8) as $report)
                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                        <div class="me-3">
                            @if($report->report_type === 'department')
                                <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fas fa-users text-white"></i>
                                </div>
                            @else
                                <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fas fa-clipboard-check text-white"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ Str::limit($report->title, 40) }}</h6>
                            <small class="text-muted">
                                {{ $report->department->name }} • {{ $report->creator->name }}
                                <br>{{ $report->created_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <div>
                            @if($report->report_type === 'department')
                                <a href="{{ route('direction.reports.show.department', $report->id) }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @else
                                <a href="{{ route('direction.reports.show.evaluation', $report->id) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Alertes et notifications -->
    <div class="row">
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Rapports Publiés</h6>
                            <h3 class="text-white">{{ $stats['published_reports'] }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Brouillons</h6>
                            <h3 class="text-white">{{ $stats['draft_reports'] }}</h3>
                        </div>
                        <i class="fas fa-edit fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">En Attente d'Examen</h6>
                            <h3 class="text-white">{{ $stats['pending_reviews'] }}</h3>
                        </div>
                        <i class="fas fa-hourglass-half fa-2x opacity-50"></i>
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
    // Graphique des statuts
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Publiés', 'Brouillons', 'Envoyés', 'Examinés'],
            datasets: [{
                data: [
                    {{ $statusStats['published'] }},
                    {{ $statusStats['draft'] }},
                    {{ $statusStats['sent'] }},
                    {{ $statusStats['reviewed'] }}
                ],
                backgroundColor: ['#28a745', '#6c757d', '#007bff', '#17a2b8'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Graphique par département
    const departmentCtx = document.getElementById('departmentChart').getContext('2d');
    const departmentChart = new Chart(departmentCtx, {
        type: 'bar',
        data: {
            labels: [
                @foreach($reportsByDepartment as $dept)
                    '{{ $dept->name }}',
                @endforeach
            ],
            datasets: [{
                label: 'Départementaux',
                data: [
                    @foreach($reportsByDepartment as $dept)
                        {{ $dept->department_reports_count }},
                    @endforeach
                ],
                backgroundColor: 'rgba(40, 167, 69, 0.8)'
            }, {
                label: 'Évaluations',
                data: [
                    @foreach($reportsByDepartment as $dept)
                        {{ $dept->evaluation_reports_count }},
                    @endforeach
                ],
                backgroundColor: 'rgba(255, 193, 7, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Graphique des tendances
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    const trendsChart = new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: [
                @foreach($monthlyStats as $month)
                    '{{ $month['month'] }}',
                @endforeach
            ],
            datasets: [{
                label: 'Départementaux',
                data: [
                    @foreach($monthlyStats as $month)
                        {{ $month['department_reports'] }},
                    @endforeach
                ],
                borderColor: 'rgba(40, 167, 69, 1)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.3
            }, {
                label: 'Évaluations',
                data: [
                    @foreach($monthlyStats as $month)
                        {{ $month['evaluation_reports'] }},
                    @endforeach
                ],
                borderColor: 'rgba(255, 193, 7, 1)',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endsection

<style>
.avatar-sm {
    width: 35px;
    height: 35px;
}
</style>