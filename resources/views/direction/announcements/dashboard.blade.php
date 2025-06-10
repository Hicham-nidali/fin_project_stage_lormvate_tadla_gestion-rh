@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Tableau de Bord - Annonces</h1>
        <div>
            <a href="{{ route('direction.announcements.create') }}" class="btn btn-primary me-2">
                <i class="fas fa-plus me-2"></i>Nouvelle Annonce
            </a>
            <a href="{{ route('direction.announcements.index') }}" class="btn btn-secondary">
                <i class="fas fa-list me-2"></i>Toutes les Annonces
            </a>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Annonces</h6>
                            <h2>{{ $totalAnnouncements }}</h2>
                        </div>
                        <i class="fas fa-bullhorn fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Publiées</h6>
                            <h2>{{ $publishedCount }}</h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Brouillons</h6>
                            <h2>{{ $draftCount }}</h2>
                        </div>
                        <i class="fas fa-edit fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Urgentes</h6>
                            <h2>{{ $urgentCount }}</h2>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Annonces récentes -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Annonces Récentes</h5>
                    <a href="{{ route('direction.announcements.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
                </div>
                <div class="card-body">
                    @if($recentAnnouncements->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentAnnouncements as $announcement)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="{{ route('direction.announcements.show', $announcement->id) }}" class="text-decoration-none">
                                                    {{ $announcement->title }}
                                                </a>
                                            </h6>
                                            <p class="mb-1 text-muted small">{{ Str::limit($announcement->content, 80) }}</p>
                                            <small class="text-muted">{{ $announcement->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div class="ms-2">
                                            <span class="badge bg-{{ $announcement->status === 'published' ? 'success' : 'warning' }}">
                                                {{ $announcement->status_label }}
                                            </span>
                                            <br>
                                            <span class="badge bg-{{ $announcement->priority_color }} mt-1">
                                                {{ $announcement->priority_label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Aucune annonce récente</p>
                            <a href="{{ route('direction.announcements.create') }}" class="btn btn-primary">
                                Créer votre première annonce
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Réunions à venir -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Réunions à Venir</h5>
                </div>
                <div class="card-body">
                    @if($upcomingMeetings->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($upcomingMeetings as $meeting)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3 text-center">
                                            <div class="bg-primary text-white rounded p-2" style="min-width: 60px;">
                                                <div class="fw-bold">{{ $meeting->meeting_date->format('d') }}</div>
                                                <div class="small">{{ $meeting->meeting_date->format('M') }}</div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="{{ route('direction.announcements.show', $meeting->id) }}" class="text-decoration-none">
                                                    {{ $meeting->title }}
                                                </a>
                                            </h6>
                                            <p class="mb-1 small">
                                                <i class="fas fa-clock me-1"></i>{{ $meeting->meeting_date->format('H:i') }}
                                                @if($meeting->meeting_location)
                                                    <br><i class="fas fa-map-marker-alt me-1"></i>{{ $meeting->meeting_location }}
                                                @endif
                                            </p>
                                            <small class="text-muted">{{ $meeting->meeting_date->diffForHumans() }}</small>
                                        </div>
                                        <div>
                                            @if($meeting->isToday())
                                                <span class="badge bg-danger">Aujourd'hui</span>
                                            @elseif($meeting->isTomorrow())
                                                <span class="badge bg-warning">Demain</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <p>Aucune réunion programmée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques de lecture -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistiques de Lecture - Top 5</h5>
                </div>
                <div class="card-body">
                    @if($readStats->count() > 0)
                        <div class="row">
                            <div class="col-lg-6">
                                <canvas id="readStatsChart" width="400" height="200"></canvas>
                            </div>
                            <div class="col-lg-6">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Annonce</th>
                                                <th class="text-center">Lu par</th>
                                                <th class="text-center">%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($readStats as $stat)
                                                <tr>
                                                    <td>{{ Str::limit($stat->title, 30) }}</td>
                                                    <td class="text-center">{{ $stat->read_count }}</td>
                                                    <td class="text-center">
                                                        {{ $totalUsers > 0 ? round(($stat->read_count / $totalUsers) * 100, 1) : 0 }}%
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-chart-bar fa-3x mb-3"></i>
                            <p>Aucune statistique de lecture disponible</p>
                            <small>Publiez des annonces pour voir les statistiques</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions Rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-grid">
                                <a href="{{ route('direction.announcements.create') }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus fa-2x mb-2"></i>
                                    <br>Créer une Annonce
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-grid">
                                <a href="{{ route('direction.announcements.index', ['status' => 'draft']) }}" class="btn btn-warning btn-lg">
                                    <i class="fas fa-edit fa-2x mb-2"></i>
                                    <br>Voir les Brouillons
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-grid">
                                <a href="{{ route('direction.announcements.index', ['priority' => 'urgent']) }}" class="btn btn-danger btn-lg">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                    <br>Annonces Urgentes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique des statistiques de lecture
    @if($readStats->count() > 0)
    const ctx = document.getElementById('readStatsChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [
                @foreach($readStats as $stat)
                    '{{ Str::limit($stat->title, 20) }}',
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($readStats as $stat)
                        {{ $stat->read_count }},
                    @endforeach
                ],
                backgroundColor: [
                    '#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: true,
                    text: 'Nombre de lectures par annonce'
                }
            }
        }
    });
    @endif
});
</script>
@endsection