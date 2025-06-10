@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-chart-line me-3"></i>Tous les Rapports du Système</h1>
        <div class="btn-group">
            <a href="{{ route('direction.reports.dashboard') }}" class="btn btn-info">
                <i class="fas fa-chart-bar me-2"></i>Tableau de Bord
            </a>
            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-download me-2"></i>Exporter
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('direction.reports.export', ['type' => 'all', 'format' => 'csv']) }}">Tous les rapports (CSV)</a></li>
                <li><a class="dropdown-item" href="{{ route('direction.reports.export', ['type' => 'department', 'format' => 'csv']) }}">Rapports départementaux (CSV)</a></li>
                <li><a class="dropdown-item" href="{{ route('direction.reports.export', ['type' => 'evaluation', 'format' => 'csv']) }}">Rapports d'évaluation (CSV)</a></li>
            </ul>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Total Rapports</h6>
                            <h3 class="text-white">{{ $totalReports }}</h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Rapports Départementaux</h6>
                            <h3 class="text-white">{{ $totalDepartmentReports }}</h3>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Rapports d'Évaluation</h6>
                            <h3 class="text-white">{{ $totalEvaluationReports }}</h3>
                        </div>
                        <i class="fas fa-clipboard-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Ce Mois</h6>
                            <h3 class="text-white">{{ $reportsThisMonth }}</h3>
                        </div>
                        <i class="fas fa-calendar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres et Recherche</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('direction.reports.index') }}">
                <div class="row">
                    <div class="col-md-2">
                        <label for="department" class="form-label">Département</label>
                        <select name="department" id="department" class="form-select">
                            <option value="">Tous</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $departmentFilter == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label">Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">Tous</option>
                            <option value="evaluation" {{ $typeFilter == 'evaluation' ? 'selected' : '' }}>Évaluation</option>
                            <option value="monthly" {{ $typeFilter == 'monthly' ? 'selected' : '' }}>Mensuel</option>
                            <option value="quarterly" {{ $typeFilter == 'quarterly' ? 'selected' : '' }}>Trimestriel</option>
                            <option value="annual" {{ $typeFilter == 'annual' ? 'selected' : '' }}>Annuel</option>
                            <option value="custom" {{ $typeFilter == 'custom' ? 'selected' : '' }}>Personnalisé</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Statut</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Tous</option>
                            <option value="draft" {{ $statusFilter == 'draft' ? 'selected' : '' }}>Brouillon</option>
                            <option value="published" {{ $statusFilter == 'published' ? 'selected' : '' }}>Publié</option>
                            <option value="sent" {{ $statusFilter == 'sent' ? 'selected' : '' }}>Envoyé</option>
                            <option value="reviewed" {{ $statusFilter == 'reviewed' ? 'selected' : '' }}>Examiné</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">Date début</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFromFilter }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">Date fin</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateToFilter }}">
                    </div>
                    <div class="col-md-2">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Titre..." value="{{ $searchFilter }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-2"></i>Filtrer
                        </button>
                        <a href="{{ route('direction.reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Rapports Départementaux -->
    @if($departmentReports->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Rapports Départementaux ({{ $departmentReports->count() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Titre</th>
                            <th>Département</th>
                            <th>Type</th>
                            <th>Créé par</th>
                            <th>Période</th>
                            <th>Statut</th>
                            <th>Date création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departmentReports as $report)
                        <tr>
                            <td>
                                <strong>{{ $report->title }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $report->department->name }}</span>
                            </td>
                            <td>
                                @switch($report->type)
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
                                        <span class="badge bg-secondary">{{ ucfirst($report->type) }}</span>
                                @endswitch
                            </td>
                            <td>{{ $report->creator->name }}</td>
                            <td>
                                @if($report->period_start && $report->period_end)
                                    {{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($report->status == 'published')
                                    <span class="badge bg-success">Publié</span>
                                @else
                                    <span class="badge bg-secondary">Brouillon</span>
                                @endif
                            </td>
                            <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('direction.reports.show.department', $report->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Rapports d'Évaluation -->
    @if($evaluationReports->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Rapports d'Évaluation ({{ $evaluationReports->count() }})</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Titre</th>
                            <th>Département</th>
                            <th>Créé par</th>
                            <th>Période d'évaluation</th>
                            <th>Statut</th>
                            <th>Envoyé le</th>
                            <th>Examiné par</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($evaluationReports as $report)
                        <tr>
                            <td>
                                <strong>{{ $report->title }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $report->department->name }}</span>
                            </td>
                            <td>{{ $report->creator->name }}</td>
                            <td>{{ $report->evaluation_period_start->format('d/m/Y') }} - {{ $report->evaluation_period_end->format('d/m/Y') }}</td>
                            <td>
                                @switch($report->status)
                                    @case('draft')
                                        <span class="badge bg-secondary">Brouillon</span>
                                        @break
                                    @case('sent')
                                        <span class="badge bg-primary">Envoyé</span>
                                        @break
                                    @case('reviewed')
                                        <span class="badge bg-success">Examiné</span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                {{ $report->sent_at ? $report->sent_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td>
                                {{ $report->reviewer ? $report->reviewer->name : '-' }}
                            </td>
                            <td>
                                <a href="{{ route('direction.reports.show.evaluation', $report->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($departmentReports->count() == 0 && $evaluationReports->count() == 0)
    <div class="card">
        <div class="card-body text-center py-5">
            <div class="text-muted">
                <i class="fas fa-chart-line fa-4x mb-3"></i>
                <h4>Aucun rapport trouvé</h4>
                <p>Aucun rapport ne correspond aux critères de recherche.</p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
// Auto-soumission du formulaire quand on change les filtres
document.querySelectorAll('#department, #type, #status').forEach(function(select) {
    select.addEventListener('change', function() {
        this.form.submit();
    });
});
</script>
@endsection