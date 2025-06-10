@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des Annonces</h1>
        <div>
            <a href="{{ route('direction.announcements.dashboard') }}" class="btn btn-info me-2">
                <i class="fas fa-chart-bar me-2"></i>Tableau de Bord
            </a>
            <a href="{{ route('direction.announcements.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouvelle Annonce
            </a>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Annonces</h6>
                            <h3>{{ $totalAnnouncements }}</h3>
                        </div>
                        <i class="fas fa-bullhorn fa-2x"></i>
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
                            <h3>{{ $publishedAnnouncements }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x"></i>
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
                            <h3>{{ $draftAnnouncements }}</h3>
                        </div>
                        <i class="fas fa-edit fa-2x"></i>
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
                            <h3>{{ $urgentAnnouncements }}</h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('direction.announcements.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Publié</option>
                            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archivé</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="priority" class="form-select">
                            <option value="">Toutes les priorités</option>
                            <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Priorité Élevée</option>
                            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">
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
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Titre</th>
                                <th>Statut</th>
                                <th>Priorité</th>
                                <th>Date Réunion</th>
                                <th>Créé le</th>
                                <th>Statistiques</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($announcements as $announcement)
                                <tr>
                                    <td>
                                        <strong>{{ $announcement->title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($announcement->content, 80) }}</small>
                                    </td>
                                    <td>
                                        @if($announcement->status === 'published')
                                            <span class="badge bg-success">{{ $announcement->status_label }}</span>
                                        @elseif($announcement->status === 'draft')
                                            <span class="badge bg-warning">{{ $announcement->status_label }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $announcement->status_label }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $announcement->priority_color }}">
                                            {{ $announcement->priority_label }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($announcement->meeting_date)
                                            <strong>{{ $announcement->meeting_date->format('d/m/Y H:i') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $announcement->meeting_date->diffForHumans() }}</small>
                                        @else
                                            <span class="text-muted">Aucune réunion</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $announcement->created_at->format('d/m/Y H:i') }}
                                        <br>
                                        <small class="text-muted">{{ $announcement->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        @if($announcement->status === 'published')
                                            <div class="text-center">
                                                <strong>{{ $announcement->getReadPercentage() }}%</strong>
                                                <br>
                                                <small>({{ $announcement->getReadCount() }}/{{ $announcement->getTotalPotentialReaders() }})</small>
                                                <br>
                                                <a href="{{ route('direction.announcements.read-stats', $announcement->id) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-chart-pie"></i> Détails
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-muted">Non publié</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('direction.announcements.show', $announcement->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($announcement->canBeEdited())
                                                <a href="{{ route('direction.announcements.edit', $announcement->id) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            
                                            @if($announcement->canBePublished())
                                                <form action="{{ route('direction.announcements.publish', $announcement->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Publier cette annonce ?')">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($announcement->canBeArchived())
                                                <form action="{{ route('direction.announcements.archive', $announcement->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Archiver cette annonce ?')">
                                                        <i class="fas fa-archive"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <form action="{{ route('direction.announcements.destroy', $announcement->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cette annonce ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $announcements->links() }}
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    Aucune annonce trouvée.
                    <br>
                    <a href="{{ route('direction.announcements.create') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus me-2"></i>Créer votre première annonce
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection