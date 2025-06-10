@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Annonce de la Direction</h1>
        <a href="{{ route('announcements.index') }}" class="btn btn-secondary">
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
                                            
                                            @if($announcement->meeting_date)
                                                <div class="mt-3">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="addToCalendar()">
                                                        <i class="fas fa-calendar-plus me-1"></i>Ajouter au Calendrier
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info ms-2" onclick="setReminder()">
                                                        <i class="fas fa-bell me-1"></i>Définir un Rappel
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Imprimer
                        </button>
                        <button class="btn btn-success" onclick="shareAnnouncement()">
                            <i class="fas fa-share me-2"></i>Partager
                        </button>
                        @if($announcement->meeting_date)
                            <button class="btn btn-info" onclick="addToCalendar()">
                                <i class="fas fa-calendar-plus me-2"></i>Ajouter au Calendrier
                            </button>
                        @endif
                        <a href="{{ route('announcements.team-reading-stats') }}" class="btn btn-outline-info">
                            <i class="fas fa-chart-line me-2"></i>Stats Équipe
                        </a>
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
                                <div class="avatar-placeholder bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
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

            <!-- Vue Chef de Département -->
            <div class="card mt-3 border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-user-tie me-2"></i>Vue Chef de Département</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">En tant que chef de département, vous pouvez :</p>
                    <div class="d-grid gap-2">
                        <a href="{{ route('announcements.team-reading-stats') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-chart-line me-1"></i>Analyser Lecture Équipe
                        </a>
                        <button class="btn btn-sm btn-outline-info" onclick="informTeam()">
                            <i class="fas fa-users me-1"></i>Informer l'Équipe
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="checkTeamAvailability()">
                            <i class="fas fa-calendar-check me-1"></i>Vérifier Disponibilités
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rappels et notifications -->
            @if($announcement->meeting_date)
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-bell me-2"></i>Rappels</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Définir des rappels pour cette réunion :</p>
                        <div class="d-grid gap-2">
                            <button class="btn btn-sm btn-outline-warning" onclick="setReminder(15)">
                                <i class="fas fa-clock me-1"></i>15 min avant
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="setReminder(60)">
                                <i class="fas fa-clock me-1"></i>1 heure avant
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="setReminder(1440)">
                                <i class="fas fa-clock me-1"></i>1 jour avant
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Navigation rapide -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-compass me-2"></i>Navigation</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('announcements.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list me-1"></i>Toutes les Annonces
                        </a>
                        <a href="{{ route('announcements.index', ['read_status' => 'unread']) }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-envelope me-1"></i>Annonces Non Lues
                        </a>
                        <a href="{{ route('announcements.index', ['priority' => 'urgent']) }}" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-exclamation-triangle me-1"></i>Annonces Urgentes
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-home me-1"></i>Tableau de Bord
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Ajouter au calendrier
function addToCalendar() {
    @if($announcement->meeting_date)
        const startDate = new Date('{{ $announcement->meeting_date->format('Y-m-d\TH:i:s') }}');
        const endDate = new Date(startDate.getTime() + (60 * 60 * 1000)); // 1 heure plus tard
        
        const title = encodeURIComponent('{{ $announcement->title }}');
        const details = encodeURIComponent('{{ strip_tags($announcement->content) }}');
        const location = encodeURIComponent('{{ $announcement->meeting_location ?? '' }}');
        
        const startStr = startDate.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
        const endStr = endDate.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
        
        const googleUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${startStr}/${endStr}&details=${details}&location=${location}`;
        
        window.open(googleUrl, '_blank');
    @endif
}

// Définir un rappel
function setReminder(minutes = null) {
    if (minutes) {
        alert(`Rappel défini pour ${minutes} minutes avant la réunion.`);
    } else {
        const customMinutes = prompt('Combien de minutes avant la réunion souhaitez-vous être rappelé ?');
        if (customMinutes && !isNaN(customMinutes)) {
            alert(`Rappel défini pour ${customMinutes} minutes avant la réunion.`);
        }
    }
}

// Partager l'annonce
function shareAnnouncement() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $announcement->title }}',
            text: '{{ Str::limit(strip_tags($announcement->content), 100) }}',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Lien copié dans le presse-papiers !');
        });
    }
}

// Informer l'équipe
function informTeam() {
    if (confirm('Envoyer un rappel à votre équipe concernant cette annonce ?')) {
        alert('Rappel envoyé à votre équipe !');
    }
}

// Vérifier disponibilités équipe
function checkTeamAvailability() {
    alert('Fonctionnalité de vérification des disponibilités à implémenter.');
}
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