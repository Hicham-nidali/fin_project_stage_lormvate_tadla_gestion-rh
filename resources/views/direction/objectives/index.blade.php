@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-bullseye me-3"></i>Gestion des Objectifs</h1>
        <div>
            <a href="{{ route('direction.objectives.dashboard') }}" class="btn btn-info me-2">
                <i class="fas fa-chart-line me-2"></i>Tableau de bord
            </a>
            <a href="{{ route('direction.objectives.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouvel Objectif
            </a>
        </div>
    </div>

    <!-- Statistiques rapides -->
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
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Actifs</h6>
                            <h3 class="text-white">{{ $stats['active'] }}</h3>
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
                            <h6 class="text-white">Critiques</h6>
                            <h3 class="text-white">{{ $stats['critical'] }}</h3>
                        </div>
                        <i class="fas fa-fire fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance par département -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Performance par Département</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Département</th>
                            <th>Chef</th>
                            <th>Total</th>
                            <th>Terminés</th>
                            <th>En retard</th>
                            <th>Taux de completion</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departmentStats as $dept)
                        <tr>
                            <td><strong>{{ $dept->name }}</strong></td>
                            <td>{{ $dept->head ? $dept->head->name : 'Non assigné' }}</td>
                            <td><span class="badge bg-primary">{{ $dept->objectives_count }}</span></td>
                            <td><span class="badge bg-success">{{ $dept->completed_objectives_count }}</span></td>
                            <td><span class="badge bg-danger">{{ $dept->overdue_objectives_count }}</span></td>
                            <td>
                                @php
                                    $rate = $dept->objectives_count > 0 ? round(($dept->completed_objectives_count / $dept->objectives_count) * 100, 1) : 0;
                                @endphp
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar 
                                        @if($rate >= 80) bg-success 
                                        @elseif($rate >= 60) bg-warning 
                                        @else bg-danger @endif" 
                                        style="width: {{ $rate }}%">
                                        {{ $rate }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($rate >= 80)
                                    <span class="badge bg-success">Excellent</span>
                                @elseif($rate >= 60)
                                    <span class="badge bg-warning">Correct</span>
                                @else
                                    <span class="badge bg-danger">À améliorer</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Filtres et liste des objectifs -->
    <div class="card">
        <div class="card-header bg-light">
            <div class="row">
                <div class="col-md-8">
                    <form action="{{ route('direction.objectives.index') }}" method="GET" class="d-flex flex-wrap">
                        <select name="department" class="form-select me-2 mb-2" style="width: auto;">
                            <option value="">Tous les départements</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        
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
                            <th>Département</th>
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
                            <td>{{ $objective->department->name }}</td>
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
                                        {{ abs($objective->days_remaining) }} jour(s) de retard
                                    </small>
                                @elseif($objective->days_until_due <= 3)
                                    <br><small class="text-warning">
                                        <i class="fas fa-clock"></i>
                                        {{ $objective->days_until_due }} jour(s) restant(s)
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
                                    <a href="{{ route('direction.objectives.show', $objective->id) }}" 
                                       class="btn btn-sm btn-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('direction.objectives.edit', $objective->id) }}" 
                                       class="btn btn-sm btn-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($objective->status !== 'cancelled')
                                        <form action="{{ route('direction.objectives.cancel', $objective->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning" 
                                                    title="Annuler"
                                                    onclick="return confirm('Annuler cet objectif ?')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
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
                    <a href="{{ route('direction.objectives.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Créer le premier objectif
                    </a>
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