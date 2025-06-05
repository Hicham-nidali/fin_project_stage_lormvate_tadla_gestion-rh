@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-bullseye me-3"></i>{{ $objective->title }}</h1>
        <div>
            <a href="{{ route('direction.objectives.edit', $objective->id) }}" class="btn btn-primary me-2">
                <i class="fas fa-edit me-2"></i>Modifier
            </a>
            <a href="{{ route('direction.objectives.index') }}" class="btn btn-secondary">
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
                            <strong>Département :</strong>
                            <p>{{ $objective->department->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Chef de département :</strong>
                            <p>{{ $objective->department->head ? $objective->department->head->name : 'Non assigné' }}</p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Type :</strong>
                            <p>{{ ucfirst($objective->type) }}</p>
                        </div>
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
                    </div>
                    
                    <div class="mb-3">
                        <strong>Description :</strong>
                        <p class="mt-2">{{ $objective->description }}</p>
                    </div>
                    
                    @if($objective->notes)
                    <div class="mb-3">
                        <strong>Notes :</strong>
                        <p class="mt-2">{{ $objective->notes }}</p>
                    </div>
                    @endif
                    
                    @if($objective->completion_notes && $objective->status === 'completed')
                    <div class="mb-3">
                        <strong>Notes de completion :</strong>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ $objective->completion_notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Métriques et KPIs -->
            @if($objective->metrics)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Métriques et KPIs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Métrique</th>
                                    <th>Objectif cible</th>
                                    <th>Unité</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($objective->metrics as $metric)
                                <tr>
                                    <td><strong>{{ $metric['name'] }}</strong></td>
                                    <td>{{ $metric['target'] ?? 'Non défini' }}</td>
                                    <td>{{ $metric['unit'] ?? '-' }}</td>
                                    <td>
                                        @if($objective->status === 'completed')
                                            <span class="badge bg-success">Atteint</span>
                                        @elseif($objective->status === 'in_progress')
                                            <span class="badge bg-warning">En cours</span>
                                        @else
                                            <span class="badge bg-secondary">En attente</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar - Progression et actions -->
        <div class="col-md-4">
            <!-- Progression -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Progression</h5>
                </div>
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <div class="progress mx-auto" style="width: 120px; height: 120px; border-radius: 50%; background: #f1f1f1;">
                            <div class="progress-bar {{ $objective->progress_bar_color }}" 
                                 style="width: {{ $objective->progress_percentage }}%; height: 120px; border-radius: 50%;">
                            </div>
                        </div>
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
                        <small class="text-muted">Progression mise à jour par le département</small>
                    @endif
                </div>
            </div>

            <!-- Informations temporelles -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Informations Temporelles</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Créé le :</strong>
                        <p class="mb-1">{{ $objective->created_at->format('d/m/Y à H:i') }}</p>
                        <small class="text-muted">par {{ $objective->creator->name }}</small>
                    </div>
                    
                    @if($objective->notification_sent)
                    <div class="mb-3">
                        <strong>Notifié le :</strong>
                        <p class="mb-1">{{ $objective->notified_at->format('d/m/Y à H:i') }}</p>
                        <small class="text-success">
                            <i class="fas fa-check-circle me-1"></i>Notification envoyée
                        </small>
                    </div>
                    @endif
                    
                    @if($objective->completed_at)
                    <div class="mb-3">
                        <strong>Terminé le :</strong>
                        <p class="mb-1">{{ $objective->completed_at->format('d/m/Y à H:i') }}</p>
                        @php
                            $completionTime = $objective->start_date->diffInDays($objective->completed_at);
                        @endphp
                        <small class="text-muted">
                            Durée de réalisation : {{ $completionTime }} jour(s)
                        </small>
                    </div>
                    @endif
                    
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
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('direction.objectives.edit', $objective->id) }}" 
                           class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Modifier l'objectif
                        </a>
                        
                        @if($objective->status !== 'cancelled')
                            <form action="{{ route('direction.objectives.cancel', $objective->id) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir annuler cet objectif ?')">
                                    <i class="fas fa-ban me-2"></i>Annuler l'objectif
                                </button>
                            </form>
                        @endif
                        
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Imprimer
                        </button>
                        
                        <hr>
                        
                        <a href="{{ route('direction.objectives.index', ['department' => $objective->department_id]) }}" 
                           class="btn btn-outline-info">
                            <i class="fas fa-building me-2"></i>Autres objectifs du département
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Actualisation automatique de la progression (toutes les 5 minutes)
setInterval(function() {
    if ({{ $objective->status === 'in_progress' ? 'true' : 'false' }}) {
        location.reload();
    }
}, 300000); // 5 minutes

// Notification en temps réel si changement de statut
document.addEventListener('DOMContentLoaded', function() {
    // Ici vous pourriez ajouter du WebSocket ou long polling
    // pour les mises à jour en temps réel
});
</script>
@endsection