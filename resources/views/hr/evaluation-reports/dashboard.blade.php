@extends('hr.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Tableau de Bord - Rapports d'Évaluation</h1>
        <a href="{{ route('hr.evaluation-reports.index') }}" class="btn btn-primary">
            <i class="fas fa-list me-2"></i>Voir tous les rapports
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
                            <h3 class="text-white">{{ $totalReports }}</h3>
                        </div>
                        <i class="fas fa-file-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">En attente d'examen</h6>
                            <h3 class="text-white">{{ $pendingReviews }}</h3>
                        </div>
                        <i class="fas fa-hourglass-half fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Examinés</h6>
                            <h3 class="text-white">{{ $reviewedReports }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Départements actifs</h6>
                            <h3 class="text-white">{{ $departmentStats->where('reports_count', '>', 0)->count() }}</h3>
                        </div>
                        <i class="fas fa-building fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rapports en attente d'examen (priorité) -->
    @if($pendingReviews > 0)
    <div class="card mb-4">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Rapports en attente d'examen ({{ $pendingReviews }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Département</th>
                            <th>Chef</th>
                            <th>Envoyé le</th>
                            <th>Urgence</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentReports as $report)
                        <tr>
                            <td>{{ $report->title }}</td>
                            <td>{{ $report->department->name }}</td>
                            <td>{{ $report->creator->name }}</td>
                            <td>{{ $report->sent_at->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $daysSinceSent = $report->sent_at->diffInDays(now());
                                @endphp
                                @if($daysSinceSent >= 7)
                                    <span class="badge bg-danger">Urgent ({{ $daysSinceSent }} jours)</span>
                                @elseif($daysSinceSent >= 3)
                                    <span class="badge bg-warning">Priorité ({{ $daysSinceSent }} jours)</span>
                                @else
                                    <span class="badge bg-info">Normal ({{ $daysSinceSent }} jours)</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('hr.evaluation-reports.review', $report->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-clipboard-check"></i>
                                </a>
                                <a href="{{ route('hr.evaluation-reports.show', $report->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Graphiques et statistiques -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Rapports par département</h5>
                </div>
                <div class="card-body">
                    <canvas id="departmentReportsChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Statut des rapports</h5>
                </div>
                <div class="card-body">
                    <canvas id="reportStatusChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau détaillé par département -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Activité par département</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Département</th>
                            <th>Chef de département</th>
                            <th>Rapports envoyés</th>
                            <th>En attente</th>
                            <th>Examinés</th>
                            <th>Dernier rapport</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departmentStats as $dept)
                        <tr>
                            <td>{{ $dept->name }}</td>
                            <td>{{ $dept->head ? $dept->head->name : 'Non assigné' }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $dept->reports_count }}</span>
                            </td>
                            <td>
                                @php
                                    $pendingCount = $dept->evaluationReports()->where('status', 'sent')->count();
                                @endphp
                                <span class="badge bg-warning">{{ $pendingCount }}</span>
                            </td>
                            <td>
                                @php
                                    $reviewedCount = $dept->evaluationReports()->where('status', 'reviewed')->count();
                                @endphp
                                <span class="badge bg-success">{{ $reviewedCount }}</span>
                            </td>
                            <td>
                                @php
                                    $lastReport = $dept->evaluationReports()->where('status', '!=', 'draft')->latest('sent_at')->first();
                                @endphp
                                {{ $lastReport ? $lastReport->sent_at->format('d/m/Y') : 'Aucun' }}
                            </td>
                            <td>
                                @if($dept->reports_count == 0)
                                    <span class="badge bg-secondary">Aucun rapport</span>
                                @elseif($pendingCount > 0)
                                    <span class="badge bg-warning">En attente</span>
                                @else
                                    <span class="badge bg-success">À jour</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Graphique des rapports par département
    const deptCtx = document.getElementById('departmentReportsChart').getContext('2d');
    const deptChart = new Chart(deptCtx, {
        type: 'bar',
        data: {
            labels: @json($departmentStats->pluck('name')),
            datasets: [{
                label: 'Nombre de rapports',
                data: @json($departmentStats->pluck('reports_count')),
                backgroundColor: 'rgba(0, 123, 255, 0.8)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Nombre de rapports'
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

    // Graphique du statut des rapports
    const statusCtx = document.getElementById('reportStatusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['En attente d\'examen', 'Examinés'],
            datasets: [{
                data: [{{ $pendingReviews }}, {{ $reviewedReports }}],
                backgroundColor: [
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(40, 167, 69, 0.8)'
                ],
                borderColor: [
                    'rgba(255, 193, 7, 1)',
                    'rgba(40, 167, 69, 1)'
                ],
                borderWidth: 1
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
</script>
@endsection