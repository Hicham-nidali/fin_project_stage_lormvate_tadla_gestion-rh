@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-bullseye me-3"></i>{{ $objective->title }}</h1>
        <div>
            @if($objective->status !== 'completed' && $objective->status !== 'cancelled')
                <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#progressModal">
                    <i class="fas fa-tasks me-2"></i>Mettre à jour progression
                </button>
                @if($objective->status !== 'completed')
                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#completeModal">
                        <i class="fas fa-check-circle me-2"></i>Marquer terminé
                    </button>
                @endif
            @endif
            <a href="{{ route('objectives.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informations principales -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Détails de l'Objectif</h5>
                    <div>
                        <span class="badge {{ $objective->status_badge['class'] }} me-2">
                            {{ $objective->status_badge['text'] }}
                        </span>
                        <span class="badge {{ $objective->priority_badge['class'] }}">
                            {{ $objective->priority_badge['text'] }}
                        </span>
                        @if($objective->is_critical)
                            <i class="fas fa-fire text-danger ms-2" title="Objectif critique"></i>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Type d'objectif :</strong>
                            <p>{{ ucfirst($objective->type) }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Assigné par :</strong>
                            <p>{{ $objective->creator->name }} (Direction)</p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Date de début :</strong>
                            <p>{{ $objective->start_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Date d'échéance :</strong>
                            <p>{{ $objective->due_date->format('d/m/Y') }}
                            @if($objective->is_overdue)
                                <span class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    ({{ abs($objective->days_remaining) }} jour(s) de retard)
                                </span>
                            @elseif($objective->days_until_due <= 3 && $objective->status !== 'completed')
                                <span class="text-warning">
                                    <i class="fas fa-clock"></i>
                                    ({{ $objective->days_until_due }} jour(s) restant(s))
                                </span>
                            @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <strong>Créé le :</strong>
                            <p>{{ $objective->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Description :</strong>
                        <div class="bg-light p-3 rounded mt-2">
                            {{ $objective->description }}
                        </div>
                    </div>
                    
                    @if($objective->notes)
                    <div class="mb-3">
                        <strong>Instructions spéciales :</strong>
                        <div class="bg-warning bg-opacity-10 p-3 rounded mt-2 border-start border-warning border-3">
                            {{ $objective->notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Métriques et KPIs -->
            @if($objective->metrics)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Métriques et Indicateurs de Performance</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($objective->metrics as $index => $metric)
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h6 class="card-title text-primary">{{ $metric['name'] }}</h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Objectif cible :</strong>
                                            <span class="h5 text-success">{{ $metric['target'] ?? 'Non défini' }}</span>
                                            @if(isset($metric['unit']))
                                                <small class="text-muted">{{ $metric['unit'] }}</small>
                                            @endif
                                        </div>
                                        <div class="text-center">
                                            @if($objective->status === 'completed')
                                                <i class="fas fa-check-circle fa-2x text-success"></i>
                                                <br><small class="text-success">Atteint</small>
                                            @elseif($objective->status === 'in_progress')
                                                <i class="fas fa-chart-line fa-2x text-warning"></i>
                                                <br><small class="text-warning">En cours</small>
                                            @else
                                                <i class="fas fa-hourglass-start fa-2x text-secondary"></i>
                                                <br><small class="text-secondary">En attente</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Historique des mises à jour -->
            @if($objective->updated_at != $objective->created_at)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6>Objectif créé</h6>
                                <p>{{ $objective->created_at->format('d/m/Y à H:i') }}</p>
                                <small class="text-muted">par {{ $objective->creator->name }}</small>
                            </div>
                        </div>
                        
                        @if($objective->notified_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6>Notification envoyée</h6>
                                <p>{{ $objective->notified_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($objective->updated_at > $objective->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6>Dernière mise à jour</h6>
                                <p>{{ $objective->updated_at->format('d/m/Y à H:i') }}</p>
                            </div>
                        </div>
                        @endif
                        
                        @if($objective->completed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6>Objectif terminé</h6>
                                <p>{{ $objective->completed_at->format('d/m/Y à H:i') }}</p>
                                @if($objective->completion_notes)
                                    <div class="alert alert-success mt-2">
                                        <strong>Notes de completion :</strong><br>
                                        {{ $objective->completion_notes }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar - Progression et statut -->
        <div class="col-md-4">
            <!-- Progression -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Progression</h5>
                </div>
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <svg width="120" height="120" class="mx-auto">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#e9ecef" stroke-width="10"/>
                            <circle cx="60" cy="60" r="50" fill="none" 
                                    stroke="{{ $objective->progress_percentage >= 80 ? '#28a745' : ($objective->progress_percentage >= 50 ? '#ffc107' : '#17a2b8') }}" 
                                    stroke-width="10"
                                    stroke-dasharray="{{ 2 * 3.14159 * 50 }}"
                                    stroke-dashoffset="{{ 2 * 3.14159 * 50 * (1 - $objective->progress_percentage / 100) }}"
                                    transform="rotate(-90 60 60)"/>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <h3 class="mb-0">{{ $objective->progress_percentage }}%</h3>
                        </div>
                    </div>
                    
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar {{ $objective->progress_bar_color }}" 
                             style="width: {{ $objective->progress_percentage }}%">
                            {{ $objective->progress_percentage }}%
                        </div>
                    </div>
                    
                    @if($objective->status !== 'completed' && $objective->status !== 'cancelled')
                        <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#progressModal">
                            <i class="fas fa-edit me-2"></i>Mettre à jour
                        </button>
                    @endif
                </div>
            </div>

            <!-- Informations de statut -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info me-2"></i>Statut</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Statut actuel :</strong>
                        <p>
                            <span class="badge {{ $objective->status_badge['class'] }} fs-6">
                                {{ $objective->status_badge['text'] }}
                            </span>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Temps restant :</strong>
                        @if($objective->status === 'completed')
                            <p class="text-success">
                                <i class="fas fa-check-circle me-1"></i>Terminé
                            </p>
                        @elseif($objective->is_overdue)
                            <p class="text-danger">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                {{ abs($objective->days_remaining) }} jour(s) de retard
                            </p>
                        @else
                            <p class="text-info">
                                <i class="fas fa-clock me-1"></i>
                                {{ $objective->days_until_due }} jour(s) restant(s)
                            </p>
                        @endif
                    </div>
                    
                    @if($objective->is_critical)
                    <div class="alert alert-warning">
                        <i class="fas fa-fire me-2"></i>
                        <strong>Objectif critique</strong><br>
                        Cet objectif nécessite une attention prioritaire.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($objective->status !== 'completed' && $objective->status !== 'cancelled')
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#completeModal">
                                <i class="fas fa-check-circle me-2"></i>Marquer comme terminé
                            </button>
                            
                            @if($objective->status === 'completed')
                                <form action="{{ route('objectives.reopen', $objective->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-warning w-100" 
                                            onclick="return confirm('Rouvrir cet objectif ?')">
                                        <i class="fas fa-redo me-2"></i>Rouvrir l'objectif
                                    </button>
                                </form>
                            @endif
                        @endif
                        
                        <a href="{{ route('objectives.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-2"></i>Tous mes objectifs
                        </a>
                        
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Imprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal mise à jour progression -->
<div class="modal fade" id="progressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mettre à jour la progression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('objectives.update-progress', $objective->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Progression (%)</label>
                        <input type="range" class="form-range" id="progressRange" 
                               name="progress_percentage" min="0" max="100" 
                               value="{{ $objective->progress_percentage }}"
                               oninput="document.getElementById('progressValue').textContent = this.value + '%'">
                        <div class="text-center mt-2">
                            <span id="progressValue" class="badge bg-primary fs-6">
                                {{ $objective->progress_percentage }}%
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes sur l'avancement</label>
                        <textarea class="form-control" name="notes" rows="4" 
                                  placeholder="Décrivez l'avancement, les résultats obtenus, les difficultés rencontrées..."></textarea>
                        <div class="form-text">Ces notes aideront la direction à comprendre votre progression.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal completion -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Marquer l'objectif comme terminé</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('objectives.complete', $objective->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Vous êtes sur le point de marquer cet objectif comme terminé.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes de completion</label>
                        <textarea class="form-control" name="completion_notes" rows="4" 
                                  placeholder="Décrivez comment l'objectif a été atteint, les résultats obtenus..."></textarea>
                        <div class="form-text">Ces informations seront transmises à la direction.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Marquer comme terminé</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -25px;
    top: 25px;
    width: 2px;
    height: calc(100% - 5px);
    background-color: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 3px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}
</style>
@endsection