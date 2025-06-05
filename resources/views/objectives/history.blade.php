@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-history me-3"></i>Historique des Objectifs</h1>
        <a href="{{ route('objectives.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour aux objectifs
        </a>
    </div>
    
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Filtres de période</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('objectives.history') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Date début</label>
                    <input type="date" class="form-control" name="start_date" 
                           value="{{ $startDate }}" id="start_date">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Date fin</label>
                    <input type="date" class="form-control" name="end_date" 
                           value="{{ $endDate }}" id="end_date">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block w-100">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques de la période -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <h3 class="text-white">{{ $periodStats['total'] }}</h3>
                    <p class="mb-0">Total Objectifs</p>
                    <small class="text-white-50">Sur la période</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <h3 class="text-white">{{ $periodStats['completed'] }}</h3>
                    <p class="mb-0">Terminés</p>
                    @php
                        $completionRate = $periodStats['total'] > 0 ? round(($periodStats['completed'] / $periodStats['total']) * 100, 1) : 0;
                    @endphp
                    <small class="text-white-50">{{ $completionRate }}% de réussite</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center">
                    <h3 class="text-white">{{ $periodStats['avg_completion_time'] }}</h3>
                    <p class="mb-0">Jours Moyens</p>
                    <small class="text-white-50">Pour terminer</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body text-center">
                    @php
                        $avgProgress = $objectives->avg('progress_percentage');
                    @endphp
                    <h3 class="text-white">{{ round($avgProgress, 1) }}%</h3>
                    <p class="mb-0">Progression Moyenne</p>
                    <small class="text-white-50">Tous objectifs</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des objectifs -->
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Objectifs de {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} à {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</h5>
            <span class="badge bg-primary">{{ $objectives->total() }} objectif(s)</span>
        </div>
        <div class="card-body">
            @if($objectives->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Objectif</th>
                                <th>Type</th>
                                <th>Priorité</th>
                                <th>Période</th>
                                <th>Progression</th>
                                <th>Statut</th>
                                <th>Temps réalisation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($objectives as $objective)
                            <tr class="{{ $objective->status === 'completed' ? 'table-success' : '' }}">
                                <td>
                                    <div>
                                        <strong>{{ $objective->title }}</strong>
                                        @if($objective->is_critical)
                                            <i class="fas fa-fire text-danger ms-1" title="Critique"></i>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ Str::limit($objective->description, 60) }}</small>
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
                                    <div class="text-center">
                                        <small class="d-block">{{ $objective->start_date->format('d/m/Y') }}</small>
                                        <i class="fas fa-arrow-down text-muted"></i>
                                        <small class="d-block">{{ $objective->due_date->format('d/m/Y') }}</small>
                                    </div>
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
                                    @if($objective->status === 'completed' && $objective->completed_at)
                                        <br><small class="text-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            {{ $objective->completed_at->format('d/m/Y') }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($objective->completed_at)
                                        @php
                                            $duration = $objective->start_date->diffInDays($objective->completed_at);
                                        @endphp
                                        <span class="badge 
                                            @if($duration <= $objective->start_date->diffInDays($objective->due_date)) bg-success 
                                            @else bg-warning @endif">
                                            {{ $duration }} jour(s)
                                        </span>
                                        @if($duration <= $objective->start_date->diffInDays($objective->due_date))
                                            <br><small class="text-success">Dans les temps</small>
                                        @else
                                            <br><small class="text-warning">Légèrement dépassé</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('objectives.show', $objective->id) }}" 
                                       class="btn btn-sm btn-info" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($objectives->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $objectives->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun objectif trouvé</h5>
                    <p class="text-muted">Aucun objectif n'a été trouvé pour la période sélectionnée.</p>
                    <a href="{{ route('objectives.index') }}" class="btn btn-primary">
                        <i class="fas fa-bullseye me-2"></i>Voir les objectifs actuels
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Analyse de performance -->
    @if($objectives->count() > 0)
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Analyse de Performance</h5>
                </div>
                <div class="card-body">
                    @php
                        $completedOnTime = $objectives->filter(function($obj) {
                            return $obj->status === 'completed' && 
                                   $obj->completed_at && 
                                   $obj->completed_at <= $obj->due_date;
                        })->count();
                        
                        $completedLate = $objectives->filter(function($obj) {
                            return $obj->status === 'completed' && 
                                   $obj->completed_at && 
                                   $obj->completed_at > $obj->due_date;
                        })->count();
                    @endphp
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Terminés à temps</span>
                            <span class="text-success">{{ $completedOnTime }}</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ $periodStats['total'] > 0 ? ($completedOnTime / $periodStats['total']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Terminés en retard</span>
                            <span class="text-warning">{{ $completedLate }}</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" 
                                 style="width: {{ $periodStats['total'] > 0 ? ($completedLate / $periodStats['total']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Non terminés</span>
                            <span class="text-danger">{{ $periodStats['total'] - $periodStats['completed'] }}</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-danger" 
                                 style="width: {{ $periodStats['total'] > 0 ? (($periodStats['total'] - $periodStats['completed']) / $periodStats['total']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Évolution</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        @if($completionRate >= 80)
                            <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
                            <h5 class="text-success">Excellente performance !</h5>
                            <p class="text-muted">Vous maintenez un excellent taux de réussite.</p>
                        @elseif($completionRate >= 60)
                            <i class="fas fa-thumbs-up fa-3x text-success mb-3"></i>
                            <h5 class="text-primary">Bonne performance</h5>
                            <p class="text-muted">Continuez sur cette lancée !</p>
                        @else
                            <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                            <h5 class="text-info">Marge de progression</h5>
                            <p class="text-muted">Opportunité d'amélioration identifiée.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection