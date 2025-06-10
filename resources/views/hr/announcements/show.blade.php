@extends('hr.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Annonce de la Direction</h1>
        <a href="{{ route('hr.announcements.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour aux Annonces
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Contenu principal de l'annonce -->
            <div class="card">
                <div class="card-header {{ $announcement->priority === 'urgent' ? 'bg-danger text-white' : ($announcement->priority === 'high' ? 'bg-warning' : 'bg-primary text-white') }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">{{ $announcement->title }}</h4>
                            <div class="d-flex align-items-center">
                                <span class="badge {{ $announcement->priority === 'urgent' ? 'bg-light text-danger' : ($announcement->priority === 'high' ? 'bg-dark' : 'bg-light text-primary') }} me-2">
                                    {{ $announcement->priority_label }}
                                </span>
                                @if($announcement->isToday())
                                    <span class="badge bg-warning text-dark">Réunion Aujourd'hui</span>
                                @elseif($announcement->isTomorrow())
                                    <span class="badge bg-info">Réunion Demain</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <small>
                                Publié le {{ $announcement->created_at->format('d/m/Y à H:i') }}
                                <br>{{ $announcement->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Vue RH spécifique -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-chart-pie me-2"></i>Impact RH</h6>
                                <p class="mb-1"><strong>Taux de lecture :</strong> {{ $announcement->getReadPercentage() }}%</p>
                                <p class="mb-0"><strong>Utilisateurs :</strong> {{ $announcement->getReadCount() }}/{{ $announcement->getTotalPotentialReaders() }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-users me-2"></i>Engagement</h6>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $announcement->getReadPercentage() >= 80 ? 'success' : ($announcement->getReadPercentage() >= 50 ? 'warning' : 'danger') }}" 
                                         role="progressbar" style="width: {{ $announcement->getReadPercentage() }}%">
                                        {{ $announcement->getReadPercentage() }}%
                                    </div>
                                </div>
                                <small class="text-muted">Objectif RH : 80% minimum</small>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu de l'annonce -->
                    <div class="announcement-content mb-4" style="font-size: 1.1rem; line-height: 1.6;">
                        {!! nl2br(e($announcement->content)) !!}
                    </div>

                    <!-- Informations de réunion -->
                    @if($announcement->meeting_date || $announcement->meeting_location)
                        <div class="meeting-section">
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert {{ $announcement->isToday() ? 'alert-danger' : ($announcement->isTomorrow() ? 'alert-warning' : 'alert-info') }} d-flex align-items-center">
                                        <i class="fas fa-calendar-alt fa-2x me-3"></i>
                                        <div>
                                            <h5 class="alert-heading mb-2">
                                                <i class="fas fa-users me-2"></i>Informations de Réunion
                                            </h5>
                                            
                                            @if($announcement->meeting_date)
                                                <div class="mb-2">
                                                    <strong>Date et Heure :</strong>
                                                    <span class="fs-5">{{ $announcement->meeting_date->format('d/m/Y à H:i') }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ $announcement->meeting_date->diffForHumans() }}</small>
                                                </div>
                                            @endif
                                            
                                            @if($announcement->meeting_location)
                                                <div class="mb-2">
                                                    <strong>Lieu :</strong>
                                                    <span class="fs-5">{{ $announcement->meeting_location }}</span>
                                                </div>
                                            @endif
                                            
                                            <!-- Actions RH spécifiques -->
                                            <div class="mt-3">
                                                <a href="{{ route('hr.announcements.global-stats') }}" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-chart-bar me-1"></i>Analyser l'Impact
                                                </a>
                                                <a href="{{ route('hr.announcements.export-stats') }}" class="btn btn-sm btn-outline-success ms-2">
                                                    <i class="fas fa-download me-1"></i>Export Statistiques
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions RH -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6>Actions Administrateur RH</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Imprimer
                        </button>
                        <a href="{{ route('hr.announcements.global-stats') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar me-2"></i>Statistiques Globales
                        </a>
                        <a href="{{ route('hr.announcements.export-stats') }}" class="btn btn-success">
                            <i class="fas fa-file-excel me-2"></i>Export Données
                        </a>
                        <button class="btn btn-outline-secondary" onclick="shareAnnouncement()">
                            <i class="fas fa-share me-2"></i>Partager
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Informations de l'annonce -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <strong>Publié par :</strong><br>
                            <div class="d-flex align-items-center mt-1">
                                <div class="avatar-placeholder bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                    {{ substr($announcement->creator->name, 0, 1) }}
                                </div>
                                <div>
                                    {{ $announcement->creator->name }}
                                    <br><small class="text-muted">Direction</small>
                                </div>
                            </div>
                        </li>
                        <li class="mb-3">
                            <strong>Date de publication :</strong><br>
                            {{ $announcement->created_at->format('d/m/Y à H:i') }}
                            <br><small class="text-muted">{{ $announcement->created_at->diffForHumans() }}</small>
                        </li>
                        <li class="mb-3">
                            <strong>Priorité :</strong><br>
                            <span class="badge bg-{{ $announcement->priority_color }} fs-6">
                                {{ $announcement->priority_label }}
                            </span>
                        </li>
                        @if($announcement->meeting_date)
                            <li class="mb-3">
                                <strong>Temps jusqu'à la réunion :</strong><br>
                                <span class="fs-6 {{ $announcement->isToday() ? 'text-danger fw-bold' : ($announcement->isTomorrow() ? 'text-warning fw-bold' : '') }}">
                                    {{ $announcement->getTimeUntilMeeting() }}
                                </span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Statistiques RH détaillées -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Analyse RH</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Taux de lecture</span>
                            <strong class="text-{{ $announcement->getReadPercentage() >= 80 ? 'success' : ($announcement->getReadPercentage() >= 50 ? 'warning' : 'danger') }}">
                                {{ $announcement->getReadPercentage() }}%
                            </strong>
                        </div>
                        <div class="progress mt-1" style="height: 10px;">
                            <div class="progress-bar bg-{{ $announcement->getReadPercentage() >= 80 ? 'success' : ($announcement->getReadPercentage() >= 50 ? 'warning' : 'danger') }}" 
                                 style="width: {{ $announcement->getReadPercentage() }}%"></div>
                        </div>
                    </div>
                    
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between mb-2">
                            <span>Utilisateurs totaux :</span>
                            <strong>{{ $announcement->getTotalPotentialReaders() }}</strong>
                        </li>
                        <li class="d-flex justify-content-between mb-2">
                            <span>Ont lu :</span>
                            <strong class="text-success">{{ $announcement->getReadCount() }}</strong>
                        </li>
                        <li class="d-flex justify-content-between mb-2">
                            <span>N'ont pas lu :</span>
                            <strong class="text-danger">{{ $announcement->getTotalPotentialReaders() - $announcement->getReadCount() }}</strong>
                        </li>
                    </ul>
                    
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="{{ route('hr.announcements.global-stats') }}" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-analytics"></i> Analyse Complète
                        </a>
                        <a href="{{ route('hr.announcements.export-stats') }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-download"></i> Export Détaillé
                        </a>
                    </div>
                </div>
            </div>

            <!-- Navigation rapide -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-compass me-2"></i>Navigation RH</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('hr.announcements.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list me-1"></i>Toutes les Annonces
                        </a>
                        <a href="{{ route('hr.announcements.index', ['read_status' => 'unread']) }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-envelope me-1"></i>Annonces Non Lues
                        </a>
                        <a href="{{ route('hr.announcements.index', ['priority' => 'urgent']) }}" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-exclamation-triangle me-1"></i>Annonces Urgentes
                        </a>
                        <a href="{{ route('hr.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-home me-1"></i>Tableau de Bord RH
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Partager l'annonce
function shareAnnouncement() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $announcement->title }}',
            text: '{{ Str::limit(strip_tags($announcement->content), 100) }}',
            url: window.location.href
        });
    } else {
        // Fallback - copier le lien
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Lien copié dans le presse-papiers !');
        });
    }
}

// Impression personnalisée
window.addEventListener('beforeprint', function() {
    document.body.classList.add('printing');
});

window.addEventListener('afterprint', function() {
    document.body.classList.remove('printing');
});
</script>

<style>
@media print {
    .btn, .card-header .badge, nav, .sidebar, #sidebar-wrapper {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .container-fluid {
        padding: 0 !important;
    }
    
    .announcement-content {
        font-size: 12pt !important;
        line-height: 1.5 !important;
    }
}
</style>
@endsection