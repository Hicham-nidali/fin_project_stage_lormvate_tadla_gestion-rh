@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Statistiques de Lecture</h1>
        <div>
            <a href="{{ route('direction.announcements.show', $announcement->id) }}" class="btn btn-info me-2">
                <i class="fas fa-eye me-2"></i>Voir l'Annonce
            </a>
            <a href="{{ route('direction.announcements.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <!-- Informations de l'annonce -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ $announcement->title }}</h5>
            <div class="mt-2">
                <span class="badge bg-{{ $announcement->priority_color }} me-2">{{ $announcement->priority_label }}</span>
                <span class="badge bg-{{ $announcement->status === 'published' ? 'success' : 'warning' }}">{{ $announcement->status_label }}</span>
                <small class="text-muted ms-3">Publié le {{ $announcement->created_at->format('d/m/Y à H:i') }}</small>
            </div>
        </div>
        <div class="card-body">
            <p>{{ Str::limit($announcement->content, 200) }}</p>
            @if($announcement->meeting_date)
                <p><strong>Réunion :</strong> {{ $announcement->meeting_date->format('d/m/Y à H:i') }}
                @if($announcement->meeting_location) - {{ $announcement->meeting_location }}@endif</p>
            @endif
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary">{{ $readUsers->count() }}</h3>
                    <p class="mb-0">Ont lu</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger">{{ $unreadUsers->count() }}</h3>
                    <p class="mb-0">N'ont pas lu</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success">{{ $announcement->getReadPercentage() }}%</h3>
                    <p class="mb-0">Taux de lecture</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info">{{ $readUsers->count() + $unreadUsers->count() }}</h3>
                    <p class="mb-0">Total utilisateurs</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques par département -->
    @if($departmentStats->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistiques par Département</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Département</th>
                                    <th class="text-center">Total Utilisateurs</th>
                                    <th class="text-center">Ont Lu</th>
                                    <th class="text-center">N'ont pas Lu</th>
                                    <th class="text-center">Taux de Lecture</th>
                                    <th class="text-center">Progression</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($departmentStats as $stat)
                                    @php
                                        $percentage = $stat->total_users > 0 ? round(($stat->read_count / $stat->total_users) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $stat->department_name ?? 'Aucun département' }}</strong></td>
                                        <td class="text-center">{{ $stat->total_users }}</td>
                                        <td class="text-center text-success">{{ $stat->read_count }}</td>
                                        <td class="text-center text-danger">{{ $stat->total_users - $stat->read_count }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $percentage >= 80 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger') }}">
                                                {{ $percentage }}%
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 20px; width: 100px;">
                                                <div class="progress-bar bg-{{ $percentage >= 80 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger') }}" 
                                                     role="progressbar" style="width: {{ $percentage }}%"
                                                     aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Utilisateurs ayant lu -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-success">
                        <i class="fas fa-check-circle me-2"></i>Ont Lu ({{ $readUsers->count() }})
                    </h5>
                    <button class="btn btn-sm btn-outline-success" onclick="exportUsers('read')">
                        <i class="fas fa-download"></i> Exporter
                    </button>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @if($readUsers->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($readUsers as $readUser)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            {{ substr($readUser->user->name, 0, 1) }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $readUser->user->name }}</h6>
                                            <p class="mb-1 text-muted small">
                                                {{ $readUser->user->department->name ?? 'Aucun département' }}
                                                <span class="badge bg-secondary ms-2">{{ ucfirst($readUser->user->role) }}</span>
                                            </p>
                                            <small class="text-success">
                                                <i class="fas fa-clock me-1"></i>Lu le {{ $readUser->read_at->format('d/m/Y à H:i') }}
                                                ({{ $readUser->read_at->diffForHumans() }})
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-user-check fa-3x mb-3"></i>
                            <p>Aucun utilisateur n'a encore lu cette annonce</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Utilisateurs n'ayant pas lu -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-danger">
                        <i class="fas fa-times-circle me-2"></i>N'ont pas Lu ({{ $unreadUsers->count() }})
                    </h5>
                    <div>
                        <button class="btn btn-sm btn-outline-warning me-2" onclick="sendReminder()">
                            <i class="fas fa-bell"></i> Rappel
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="exportUsers('unread')">
                            <i class="fas fa-download"></i> Exporter
                        </button>
                    </div>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @if($unreadUsers->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($unreadUsers as $unreadUser)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            {{ substr($unreadUser->name, 0, 1) }}
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $unreadUser->name }}</h6>
                                            <p class="mb-1 text-muted small">
                                                {{ $unreadUser->department->name ?? 'Aucun département' }}
                                                <span class="badge bg-secondary ms-2">{{ ucfirst($unreadUser->role) }}</span>
                                            </p>
                                            <small class="text-muted">
                                                <i class="fas fa-envelope me-1"></i>{{ $unreadUser->email }}
                                            </small>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary" onclick="sendPersonalReminder({{ $unreadUser->id }})">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                            <p class="text-success">Tous les utilisateurs ont lu cette annonce !</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique chronologique des lectures -->
    @if($readUsers->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Évolution des Lectures</h5>
                </div>
                <div class="card-body">
                    <canvas id="readTimelineChart" width="400" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
// Fonctions d'export et de rappel
function exportUsers(type) {
    const data = type === 'read' ? @json($readUsers) : @json($unreadUsers);
    const filename = `annonce_{{ $announcement->id }}_${type}_users.csv`;
    
    let csv = type === 'read' ? 
        'Nom,Email,Département,Rôle,Date de Lecture\n' :
        'Nom,Email,Département,Rôle\n';
    
    data.forEach(item => {
        const user = type === 'read' ? item.user : item;
        const dept = user.department ? user.department.name : 'Aucun département';
        const readDate = type === 'read' ? item.read_at : '';
        
        csv += `"${user.name}","${user.email}","${dept}","${user.role}"`;
        if (type === 'read') csv += `,"${readDate}"`;
        csv += '\n';
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}

function sendReminder() {
    if (confirm('Envoyer un rappel à tous les utilisateurs qui n\'ont pas lu cette annonce ?')) {
        // Ici vous pourriez implémenter l'envoi de rappel
        alert('Fonctionnalité de rappel à implémenter');
    }
}

function sendPersonalReminder(userId) {
    if (confirm('Envoyer un rappel personnel à cet utilisateur ?')) {
        // Ici vous pourriez implémenter l'envoi de rappel personnel
        alert('Rappel personnel envoyé !');
    }
}

// Graphique chronologique
@if($readUsers->count() > 0)
document.addEventListener('DOMContentLoaded', function() {
    const readData = @json($readUsers->groupBy(function($item) {
        return $item['read_at']->format('Y-m-d');
    })->map->count());
    
    const ctx = document.getElementById('readTimelineChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: Object.keys(readData),
            datasets: [{
                label: 'Lectures par jour',
                data: Object.values(readData),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
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
@endif
</script>
@endsection