@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-chart-line me-3"></i>Tableau de Bord - Objectifs</h1>
        <div>
            <a href="{{ route('direction.objectives.create') }}" class="btn btn-primary me-2">
                <i class="fas fa-plus me-2"></i>Nouvel Objectif
            </a>
            <a href="{{ route('direction.objectives.index') }}" class="btn btn-info">
                <i class="fas fa-list me-2"></i>Tous les objectifs
            </a>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total Objectifs</h6>
                            <h2 class="text-white">{{ $totalObjectives }}</h2>
                        </div>
                        <i class="fas fa-bullseye fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Actifs</h6>
                            <h2 class="text-white">{{ $activeObjectives }}</h2>
                            <small class="text-white">En cours de réalisation</small>
                        </div>
                        <i class="fas fa-play fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Terminés ce mois</h6>
                            <h2 class="text-white">{{ $completedThisMonth }}</h2>
                            <small class="text-white">Objectifs atteints</small>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">En retard</h6>
                            <h2 class="text-white">{{ $overdueObjectives }}</h2>
                            <small class="text-white">Nécessitent attention</small>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertes critiques -->
    @if($criticalObjectives->count() > 0)
    <div class="card mb-4 border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">
                <i class="fas fa-fire me-2"></i>Objectifs Critiques Nécessitant Attention
                <span class="badge bg-light text-danger">{{ $criticalObjectives->count() }}</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($criticalObjectives as $critical)
                <div class="col-md-6 mb-3">
                    <div class="card border-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="card-title text-danger">{{ $critical->title }}</h6>
                                    <p class="text-muted">{{ $critical->department->name }}</p>
                                    <p class="card-text">{{ Str::limit($critical->description, 80) }}</p>
                                </div>
                                <span class="badge {{ $critical->priority_badge['class'] }}">
                                    {{ $critical->priority_badge['text'] }}
                                </span>
                            </div>
                            
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-danger" style="width: {{ $critical->progress_percentage }}%">
                                    {{ $critical->progress_percentage }}%
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Échéance : {{ $critical->due_date->format('d/m/Y') }}
                                    @if($critical->is_overdue)
                                        <span class="text-danger">({{ abs($critical->days_remaining) }}j de retard)</span>
                                    @endif
                                </small>
                                <a href="{{ route('direction.objectives.show', $critical->id) }}" class="btn btn-sm btn-danger">
                                    Voir détails
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

    <!-- Performance par département -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Performance par Département</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($departmentPerformance as $performance)
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">{{ $performance['department']->name }}</h6>
                                <div class="text-center">
                                    <h5 class="mb-0 
                                        @if($performance['performance_score'] >= 80) text-success 
                                        @elseif($performance['performance_score'] >= 60) text-warning 
                                        @else text-danger @endif">
                                        {{ $performance['performance_score'] }}
                                    </h5>
                                    <small class="text-muted">Score</small>
                                </div>
                            </div>
                            
                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <small class="text-muted">Total</small>
                                    <div class="h6">{{ $performance['total'] }}</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">Terminés</small>
                                    <div class="h6 text-success">{{ $performance['completed'] }}</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">En retard</small>
                                    <div class="h6 text-danger">{{ $performance['overdue'] }}</div>
                                </div>
                            </div>
                            
                            <div class="progress mb-2" style="height: 25px;">
                                <div class="progress-bar 
                                    @if($performance['completion_rate'] >= 80) bg-success 
                                    @elseif($performance['completion_rate'] >= 60) bg-warning 
                                    @else bg-danger @endif" 
                                    style="width: {{ $performance['completion_rate'] }}%">
                                    {{ $performance['completion_rate'] }}%
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Taux de completion</small>
                                <a href="{{ route('direction.objectives.index', ['department' => $performance['department']->id]) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    Voir objectifs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Objectifs récents -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Objectifs Récents</h5>
                    <a href="{{ route('direction.objectives.index') }}" class="btn btn-sm btn-primary">Voir tout</a>
                </div>
                <div class="card-body">
                    @if($recentObjectives->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentObjectives as $objective)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $objective->title }}</h6>
                                        <p class="mb-1 text-muted">{{ $objective->department->name }}</p>
                                        <small class="text-muted">
                                            Créé {{ $objective->created_at->diffForHumans() }} - 
                                            Échéance {{ $objective->due_date->format('d/m/Y') }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge {{ $objective->priority_badge['class'] }} mb-1">
                                            {{ $objective->priority_badge['text'] }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $objective->progress_percentage }}%</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun objectif récent.</p>
                            <a href="{{ route('direction.objectives.create') }}" class="btn btn-primary">
                                Créer le premier objectif
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Échéances proches -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-exclamation me-2"></i>Échéances Proches
                        <small class="text-muted">(7 prochains jours)</small>
                    </h5>
                </div>
                <div class="card-body">
                    @if($upcomingDeadlines->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($upcomingDeadlines as $upcoming)
                            <div class="list-group-item px-0 
                                @if($upcoming->days_until_due <= 1) border-start border-danger border-3
                                @elseif($upcoming->days_until_due <= 3) border-start border-warning border-3
                                @else border-start border-info border-3 @endif">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $upcoming->title }}</h6>
                                        <p class="mb-1 text-muted">{{ $upcoming->department->name }}</p>
                                        <small class="
                                            @if($upcoming->days_until_due <= 1) text-danger
                                            @elseif($upcoming->days_until_due <= 3) text-warning
                                            @else text-info @endif">
                                            <i class="fas fa-clock me-1"></i>
                                            @if($upcoming->days_until_due == 0)
                                                Échéance aujourd'hui
                                            @elseif($upcoming->days_until_due == 1)
                                                Échéance demain
                                            @else
                                                {{ $upcoming->days_until_due }} jours restants
                                            @endif
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        @if($upcoming->is_critical)
                                            <i class="fas fa-fire text-danger mb-1"></i><br>
                                        @endif
                                        <div class="progress" style="width: 60px; height: 8px;">
                                            <div class="progress-bar bg-info" style="width: {{ $upcoming->progress_percentage }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ $upcoming->progress_percentage }}%</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-3">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>{{ $upcomingDeadlines->count() }}</strong> objectif(s) arrivent à échéance prochainement.
                                Assurez-vous que les départements sont informés.
                            </div>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-calendar-check fa-3x text-success mb-3"></i>
                            <p class="text-muted">Aucune échéance proche.</p>
                            <small class="text-success">Tous les objectifs sont dans les temps !</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions Rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('direction.objectives.create') }}" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-plus me-2"></i>Créer un objectif
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('direction.objectives.index', ['status' => 'overdue']) }}" class="btn btn-danger w-100 mb-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>Objectifs en retard ({{ $overdueObjectives }})
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('direction.objectives.index', ['priority' => 'critical']) }}" class="btn btn-warning w-100 mb-2">
                                <i class="fas fa-fire me-2"></i>Objectifs critiques ({{ $criticalObjectives->count() }})
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('direction.objectives.report') }}" class="btn btn-info w-100 mb-2">
                                <i class="fas fa-chart-line me-2"></i>Générer rapport
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-refresh toutes les 5 minutes
setInterval(function() {
    // Refresh seulement les notifications importantes
    const criticalCount = {{ $criticalObjectives->count() }};
    const overdueCount = {{ $overdueObjectives }};
    
    if (criticalCount > 0 || overdueCount > 0) {
        // Vous pouvez ajouter ici un indicateur visuel de refresh
        console.log('Vérification des objectifs critiques...');
    }
}, 300000); // 5 minutes
</script>
@endsection