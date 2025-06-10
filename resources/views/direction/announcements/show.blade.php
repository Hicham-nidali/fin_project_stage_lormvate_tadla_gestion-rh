@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Détails de l'Annonce</h1>
        <div>
            <a href="{{ route('direction.announcements.read-stats', $announcement->id) }}" class="btn btn-info me-2">
                <i class="fas fa-chart-pie me-2"></i>Statistiques Détaillées
            </a>
            <a href="{{ route('direction.announcements.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Contenu de l'annonce -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $announcement->title }}</h5>
                        <div class="mt-2">
                            <span class="badge bg-{{ $announcement->priority_color }} me-2">
                                {{ $announcement->priority_label }}
                            </span>
                            <span class="badge bg-{{ $announcement->status === 'published' ? 'success' : 'warning' }}">
                                {{ $announcement->status_label }}
                            </span>
                        </div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">
                            Créé le {{ $announcement->created_at->format('d/m/Y à H:i') }}
                            <br>{{ $announcement->created_at->diffForHumans() }}
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="announcement-content">
                        {!! nl2br(e($announcement->content)) !!}
                    </div>
                    
                    @if($announcement->meeting_date || $announcement->meeting_location)
                    <hr>
                    <div class="meeting-info">
                        <h6><i class="fas fa-calendar-alt me-2"></i>Informations de Réunion</h6>
                        <div class="row">
                            @if($announcement->meeting_date)
                            <div class="col-md-6">
                                <strong>Date et heure :</strong><br>
                                {{ $announcement->meeting_date->format('d/m/Y à H:i') }}
                                <br>
                                <small class="text-muted">{{ $announcement->meeting_date->diffForHumans() }}</small>
                                @if($announcement->isToday())
                                    <span class="badge bg-danger ms-2">Aujourd'hui</span>
                                @elseif($announcement->isTomorrow())
                                    <span class="badge bg-warning ms-2">Demain</span>
                                @endif
                            </div>
                            @endif
                            @if($announcement->meeting_location)
                            <div class="col-md-6">
                                <strong>Lieu :</strong><br>
                                {{ $announcement->meeting_location }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        @if($announcement->canBeEdited())
                            <a href="{{ route('direction.announcements.edit', $announcement->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Modifier
                            </a>
                        @endif
                        
                        @if($announcement->canBePublished())
                            <form action="{{ route('direction.announcements.publish', $announcement->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success" onclick="return confirm('Publier cette annonce ?')">
                                    <i class="fas fa-paper-plane me-2"></i>Publier
                                </button>
                            </form>
                        @endif
                        
                        @if($announcement->canBeArchived())
                            <form action="{{ route('direction.announcements.archive', $announcement->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-secondary" onclick="return confirm('Archiver cette annonce ?')">
                                    <i class="fas fa-archive me-2"></i>Archiver
                                </button>
                            </form>
                        @endif
                        
                        <form action="{{ route('direction.announcements.destroy', $announcement->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer définitivement cette annonce ?')">
                                <i class="fas fa-trash me-2"></i>Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Statistiques de lecture -->
            @if($announcement->status === 'published')
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistiques de Lecture</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $announcement->getReadPercentage() }}%"
                                 aria-valuenow="{{ $announcement->getReadPercentage() }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                                {{ $announcement->getReadPercentage() }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $announcement->getReadCount() }} sur {{ $totalUsers }} utilisateurs ont lu l'annonce
                        </small>
                    </div>
                    
                    <ul class="list-unstyled">
                        <li class="d-flex justify-content-between mb-2">
                            <span>Utilisateurs qui ont lu :</span>
                            <strong class="text-success">{{ $readUsers->count() }}</strong>
                        </li>
                        <li class="d-flex justify-content-between mb-2">
                            <span>Utilisateurs qui n'ont pas lu :</span>
                            <strong class="text-danger">{{ $unreadUsers->count() }}</strong>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Utilisateurs ayant lu (aperçu) -->
            @if($readUsers->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Derniers Lecteurs</h6>
                </div>
                <div class="card-body">
                    @foreach($readUsers->take(5) as $readUser)
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar-placeholder bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                {{ substr($readUser->user->name, 0, 1) }}
                            </div>
                            <div class="flex-grow-1">
                                <small>
                                    <strong>{{ $readUser->user->name }}</strong>
                                    <br>
                                    <span class="text-muted">{{ $readUser->read_at->diffForHumans() }}</span>
                                </small>
                            </div>
                        </div>
                    @endforeach
                    
                    @if($readUsers->count() > 5)
                        <div class="text-center">
                            <a href="{{ route('direction.announcements.read-stats', $announcement->id) }}" class="btn btn-sm btn-outline-primary">
                                Voir tous les lecteurs ({{ $readUsers->count() }})
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Utilisateurs n'ayant pas lu (aperçu) -->
            @if($unreadUsers->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-user-clock me-2"></i>N'ont pas encore lu</h6>
                </div>
                <div class="card-body">
                    @foreach($unreadUsers->take(5) as $unreadUser)
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar-placeholder bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                {{ substr($unreadUser->name, 0, 1) }}
                            </div>
                            <div class="flex-grow-1">
                                <small>
                                    <strong>{{ $unreadUser->name }}</strong>
                                    <br>
                                    <span class="text-muted">{{ $unreadUser->department->name ?? 'Aucun département' }}</span>
                                </small>
                            </div>
                        </div>
                    @endforeach
                    
                    @if($unreadUsers->count() > 5)
                        <div class="text-center">
                            <small class="text-muted">Et {{ $unreadUsers->count() - 5 }} autres...</small>
                        </div>
                    @endif
                </div>
            </div>
            @endif
            @else
            <!-- Annonce non publiée -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Statut</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Cette annonce n'est pas encore publiée. Elle n'est visible que par vous.
                    </div>
                    @if($announcement->canBePublished())
                        <form action="{{ route('direction.announcements.publish', $announcement->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Publier cette annonce maintenant ?')">
                                <i class="fas fa-paper-plane me-2"></i>Publier Maintenant
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endif

            <!-- Informations générales -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Créé par :</strong><br>
                            {{ $announcement->creator->name }}
                        </li>
                        <li class="mb-2">
                            <strong>Date de création :</strong><br>
                            {{ $announcement->created_at->format('d/m/Y à H:i') }}
                        </li>
                        @if($announcement->updated_at != $announcement->created_at)
                        <li class="mb-2">
                            <strong>Dernière modification :</strong><br>
                            {{ $announcement->updated_at->format('d/m/Y à H:i') }}
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection