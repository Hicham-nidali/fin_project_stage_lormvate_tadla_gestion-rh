@extends('employee.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Annonces de la Direction</h1>
        <div>
            @if($unreadCount > 0)
                <button type="button" class="btn btn-outline-success me-2" onclick="markAllAsRead()">
                    <i class="fas fa-check-double me-2"></i>Tout marquer comme lu
                </button>
            @endif
            <span class="badge bg-primary fs-6">{{ $totalAnnouncements }} annonce(s)</span>
        </div>
    </div>

    <!-- Alertes importantes -->
    @if($urgentUnreadCount > 0)
        <div class="alert alert-danger d-flex align-items-center mb-4">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Annonces Urgentes Non Lues</h5>
                <p class="mb-0">Vous avez <strong>{{ $urgentUnreadCount }}</strong> annonce(s) urgente(s) non lue(s) de la direction.</p>
            </div>
        </div>
    @endif

    @if($todayMeetings->count() > 0)
        <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="fas fa-calendar-day fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Réunions Aujourd'hui</h5>
                <p class="mb-2">{{ $todayMeetings->count() }} réunion(s) programmée(s) aujourd'hui :</p>
                @foreach($todayMeetings as $meeting)
                    <div class="mb-1">
                        <strong>{{ $meeting->title }}</strong> à {{ $meeting->meeting_date->format('H:i') }}
                        @if($meeting->meeting_location) - {{ $meeting->meeting_location }}@endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ $totalAnnouncements }}</h3>
                    <p class="mb-0">Total Annonces</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3>{{ $unreadCount }}</h3>
                    <p class="mb-0">Non Lues</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3>{{ $urgentUnreadCount }}</h3>
                    <p class="mb-0">Urgentes Non Lues</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('employee.announcements.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Priorité</label>
                        <select name="priority" class="form-select">
                            <option value="">Toutes</option>
                            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Priorité Élevée</option>
                            <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Statut de lecture</label>
                        <select name="read_status" class="form-select">
                            <option value="">Toutes</option>
                            <option value="unread" {{ request('read_status') === 'unread' ? 'selected' : '' }}>Non lues</option>
                            <option value="read" {{ request('read_status') === 'read' ? 'selected' : '' }}>Lues</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Recherche</label>
                        <input type="text" name="search" class="form-control" placeholder="Titre ou contenu..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des annonces -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Liste des Annonces</h5>
        </div>
        <div class="card-body">
            @if($announcements->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($announcements as $announcement)
                        @php
                            $isRead = $announcement->reads->count() > 0;
                        @endphp
                        <div class="list-group-item {{ !$isRead ? 'list-group-item-warning' : '' }} {{ $announcement->priority === 'urgent' ? 'border-start border-danger border-4' : '' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h6 class="mb-0 me-2">
                                            <a href="{{ route('employee.announcements.show', $announcement->id) }}" 
                                               class="text-decoration-none {{ !$isRead ? 'fw-bold' : '' }}">
                                                {{ $announcement->title }}
                                            </a>
                                        </h6>
                                        
                                        <!-- Badges de priorité -->
                                        <span class="badge bg-{{ $announcement->priority_color }} me-2">
                                            {{ $announcement->priority_label }}
                                        </span>
                                        
                                        @if(!$isRead)
                                            <span class="badge bg-warning">Non lu</span>
                                        @endif
                                        
                                        @if($announcement->meeting_date && $announcement->isToday())
                                            <span class="badge bg-danger ms-2">Réunion aujourd'hui</span>
                                        @elseif($announcement->meeting_date && $announcement->isTomorrow())
                                            <span class="badge bg-warning ms-2">Réunion demain</span>
                                        @endif
                                    </div>
                                    
                                    <p class="mb-2 text-muted">{{ Str::limit($announcement->content, 120) }}</p>
                                    
                                    @if($announcement->meeting_date || $announcement->meeting_location)
                                        <div class="meeting-info bg-light p-2 rounded mb-2">
                                            <small>
                                                <i class="fas fa-calendar-alt me-1"></i>
                                                @if($announcement->meeting_date)
                                                    <strong>{{ $announcement->meeting_date->format('d/m/Y à H:i') }}</strong>
                                                    <span class="text-muted">({{ $announcement->meeting_date->diffForHumans() }})</span>
                                                @endif
                                                @if($announcement->meeting_location)
                                                    <br><i class="fas fa-map-marker-alt me-1"></i>{{ $announcement->meeting_location }}
                                                @endif
                                            </small>
                                        </div>
                                    @endif
                                    
                                    <div class="d-flex align-items-center text-muted">
                                        <small>
                                            <i class="fas fa-user me-1"></i>{{ $announcement->creator->name }}
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-clock me-1"></i>{{ $announcement->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="text-end">
                                    @if(!$isRead)
                                        <button type="button" class="btn btn-sm btn-outline-success mb-2" 
                                                onclick="markAsRead({{ $announcement->id }})">
                                            <i class="fas fa-check"></i> Marquer comme lu
                                        </button>
                                    @else
                                        <div class="text-success mb-2">
                                            <i class="fas fa-check-circle"></i> Lu
                                        </div>
                                    @endif
                                    
                                    <div>
                                        <a href="{{ route('employee.announcements.show', $announcement->id) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Voir
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $announcements->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune annonce trouvée</h5>
                    <p class="text-muted">Il n'y a actuellement aucune annonce de la direction.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Marquer une annonce comme lue
function markAsRead(announcementId) {
    fetch(`/employee/announcements/${announcementId}/mark-read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

// Marquer toutes les annonces comme lues
function markAllAsRead() {
    if (!confirm('Marquer toutes les annonces comme lues ?')) return;
    
    fetch('/employee/announcements/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`${data.count} annonce(s) marquée(s) comme lue(s)`);
            location.reload();
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

// Auto-refresh du nombre d'annonces non lues toutes les 2 minutes
setInterval(function() {
    fetch('/employee/announcements/unread-count')
        .then(response => response.json())
        .then(data => {
            // Mettre à jour les badges de notification dans le layout
            const badges = document.querySelectorAll('.announcement-notification-badge');
            badges.forEach(badge => {
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }
            });
        })
        .catch(error => console.error('Erreur:', error));
}, 120000); // 2 minutes
</script>
@endsection