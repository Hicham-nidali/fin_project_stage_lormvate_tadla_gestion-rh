@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-bullseye me-3"></i>Mes Objectifs</h1>
        <div>
            <a href="{{ route('objectives.dashboard') }}" class="btn btn-info">
                <i class="fas fa-chart-line me-2"></i>Tableau de bord
            </a>
        </div>
    </div>

    <!-- Alertes importantes -->
 @if(isset($alerts) && count($alerts) > 0)
        @foreach($alerts as $alert)
        <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ $alert['message'] }}
            <a href="{{ $alert['action'] }}" class="alert-link ms-2">Voir</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endforeach
    @endif

    <!-- Nouveaux objectifs -->
    @if($newObjectives->count() > 0)
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-bell me-2"></i>Nouveaux Objectifs Assignés
                <span class="badge bg-light text-primary">{{ $newObjectives->count() }}</span>
            </h5>
        </div>
        <div class="card-body">
            @foreach($newObjectives as $objective)
            <div class="border-start border-primary border-3 ps-3 mb-3">
                <h6 class="mb-1">{{ $objective->title }}</h6>
                <p class="text-muted mb-1">{{ Str::limit($objective->description, 100) }}</p>
                <small class="text-muted">
                    Assigné le {{ $objective->created_at->format('d/m/Y') }} - 
                    Échéance : {{ $objective->due_date->format('d/m/Y') }}
                </small>
                <div class="mt-2">
                    <a href="{{ route('objectives.show', $objective->id) }}" class="btn btn-sm btn-primary">
                        Voir l'objectif
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total</h6>
                            <h3 class="text-white">{{ $stats['total'] }}</h3>
                        </div>
                        <i class="fas fa-bullseye fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Assignés</h6>
                            <h3 class="text-white">{{ $stats['assigned'] }}</h3>
                        </div>
                        <i class="fas fa-clipboard fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">En cours</h6>
                            <h3 class="text-white">{{ $stats['in_progress'] }}</h3>
                        </div>
                        <i class="fas fa-play fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Terminés</h6>
                            <h3 class="text-white">{{ $stats['completed'] }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">En retard</h6>
                            <h3 class="text-white">{{ $stats['overdue'] }}</h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-dark text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Taux completion</h6>
                            <h3 class="text-white">{{ $completionRate }}%</h3>
                        </div>
                        <i class="fas fa-percentage fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Objectifs critiques -->
    @if($criticalObjectives->count() > 0)
    <div class="card mb-4 border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">
                <i class="fas fa-fire me-2"></i>Objectifs Critiques
                <span class="badge bg-light text-danger">{{ $criticalObjectives->count() }}</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($criticalObjectives as $critical)
                <div class="col-md-6 mb-3">
                    <div class="card border-danger">
                        <div class="card-body">
                            <h6 class="card-title">{{ $critical->title }}</h6>
                            <p class="card-text">{{ Str::limit($critical->description, 80) }}</p>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-danger" style="width: {{ $critical->progress_percentage }}%">
                                    {{ $critical->progress_percentage }}%
                                </div>
                            </div>
                            <small class="text-muted">
                                Échéance : {{ $critical->due_date->format('d/m/Y') }}
                                @if($critical->is_overdue)
                                    <span class="text-danger">({{ abs($critical->days_remaining) }}j de retard)</span>
                                @endif
                            </small>
                            <div class="mt-2">
                                <a href="{{ route('objectives.show', $critical->id) }}" class="btn btn-sm btn-danger">
                                    Voir l'objectif
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Filtres et liste -->
    <div class="card">
        <div class="card-header bg-light">
            <div class="row">
                <div class="col-md-8">
                    <form action="{{ route('objectives.index') }}" method="GET" class="d-flex flex-wrap">
                        <select name="status" class="form-select me-2 mb-2" style="width: auto;">
                            <option value="">Tous les statuts</option>
                            <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigné</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En cours</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Terminé</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>En retard</option>
                        </select>
                        
                        <select name="priority" class="form-select me-2 mb-2" style="width: auto;">
                            <option value="">Toutes les priorités</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Faible</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Moyenne</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Haute</option>
                            <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Critique</option>
                        </select>
                        
                        <button type="submit" class="btn btn-primary mb-2">Filtrer</button>
                    </form>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Rechercher..." id="objective-search">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Objectif</th>
                            <th>Type</th>
                            <th>Priorité</th>
                            <th>Échéance</th>
                            <th>Progression</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($objectives as $objective)
                        <tr class="{{ $objective->is_overdue ? 'table-warning' : '' }}">
                            <td>
                                <div>
                                    <strong>{{ $objective->title }}</strong>
                                    @if($objective->is_critical)
                                        <i class="fas fa-fire text-danger ms-2" title="Critique"></i>
                                    @endif
                                </div>
                                <small class="text-muted">{{ Str::limit($objective->description, 50) }}</small>
                            </td>
                            <td>
                                @switch($objective->type)
                                    @case('monthly')
                                        <span class="badge bg-info">Mensuel</span>
                                        @break
                                    @case('quarterly')
                                        <span class="badge bg-primary">Trimestriel</span>
                                        @break
                                    @case('annual')
                                        <span class="badge bg-success">Annuel</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">Personnalisé</span>
                                @endswitch
                            </td>
                            <td>
                                <span class="badge {{ $objective->priority_badge['class'] }}">
                                    {{ $objective->priority_badge['text'] }}
                                </span>
                            </td>
                            <td>
                                {{ $objective->due_date->format('d/m/Y') }}
                                @if($objective->is_overdue)
                                    <br><small class="text-danger">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        {{ abs($objective->days_remaining) }}j de retard
                                    </small>
                                @elseif($objective->days_until_due <= 3)
                                    <br><small class="text-warning">
                                        <i class="fas fa-clock"></i>
                                        {{ $objective->days_until_due }}j restant(s)
                                    </small>
                                @endif
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $objective->progress_bar_color }}" 
                                         style="width: {{ $objective->progress_percentage }}%">
                                        {{ $objective->progress_percentage }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $objective->status_badge['class'] }}">
                                    {{ $objective->status_badge['text'] }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('objectives.show', $objective->id) }}" 
                                       class="btn btn-sm btn-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($objective->status !== 'completed' && $objective->status !== 'cancelled')
                                        <button type="button" class="btn btn-sm btn-success" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#progressModal{{ $objective->id }}"
                                                title="Mettre à jour la progression">
                                            <i class="fas fa-tasks"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($objectives->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun objectif trouvé avec ces critères.</p>
                </div>
            @endif
            
            <!-- Pagination -->
            @if($objectives->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $objectives->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modals de progression -->
@foreach($objectives as $objective)
@if($objective->status !== 'completed' && $objective->status !== 'cancelled')
<div class="modal fade" id="progressModal{{ $objective->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mettre à jour la progression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('objectives.update-progress', $objective->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <h6>{{ $objective->title }}</h6>
                    <div class="mb-3">
                        <label class="form-label">Progression (%)</label>
                        <input type="range" class="form-range" id="progress{{ $objective->id }}" 
                               name="progress_percentage" min="0" max="100" 
                               value="{{ $objective->progress_percentage }}"
                               oninput="document.getElementById('progressValue{{ $objective->id }}').textContent = this.value + '%'">
                        <div class="text-center">
                            <span id="progressValue{{ $objective->id }}" class="badge bg-primary">
                                {{ $objective->progress_percentage }}%
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (optionnel)</label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Décrivez l'avancement, les difficultés rencontrées..."></textarea>
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
@endif
@endforeach

@endsection

@section('scripts')
<script>
// Recherche en temps réel
document.getElementById('objective-search').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>
@endsection