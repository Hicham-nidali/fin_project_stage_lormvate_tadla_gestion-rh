@extends('hr.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Rapports d'Évaluation - Administration RH</h1>
        <a href="{{ route('hr.evaluation-reports.dashboard') }}" class="btn btn-info">
            <i class="fas fa-chart-line me-2"></i>Tableau de bord
        </a>
    </div>
    
    <div class="card">
        <div class="card-header bg-light">
            <div class="row">
                <div class="col-md-8">
                    <form action="{{ route('hr.evaluation-reports.index') }}" method="GET" class="d-flex">
                        <select name="status" class="form-select me-2" style="width: auto;" onchange="this.form.submit()">
                            <option value="">Tous les statuts</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>En attente d'examen</option>
                            <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Examinés</option>
                        </select>
                        <select name="department" class="form-select me-2" style="width: auto;" onchange="this.form.submit()">
                            <option value="">Tous les départements</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Rechercher..." id="report-search">
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
                            <th>Titre</th>
                            <th>Département</th>
                            <th>Chef de département</th>
                            <th>Période</th>
                            <th>Envoyé le</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                        <tr>
                            <td>{{ $report->title }}</td>
                            <td>{{ $report->department->name }}</td>
                            <td>{{ $report->creator->name }}</td>
                            <td>{{ $report->evaluation_period_start->format('d/m/Y') }} - {{ $report->evaluation_period_end->format('d/m/Y') }}</td>
                            <td>{{ $report->sent_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($report->status == 'sent')
                                    <span class="badge bg-warning">En attente d'examen</span>
                                @else
                                    <span class="badge bg-success">Examiné</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('hr.evaluation-reports.show', $report->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($report->status == 'sent')
                                    <a href="{{ route('hr.evaluation-reports.review', $report->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-clipboard-check"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($reports->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Aucun rapport d'évaluation reçu.
                </div>
            @endif
        </div>
    </div>
    
    <!-- Statistiques rapides -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">En attente</h6>
                            <h3 class="text-white mb-0">{{ $reports->where('status', 'sent')->count() }}</h3>
                        </div>
                        <i class="fas fa-hourglass-half fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Examinés</h6>
                            <h3 class="text-white mb-0">{{ $reports->where('status', 'reviewed')->count() }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Total</h6>
                            <h3 class="text-white mb-0">{{ $reports->count() }}</h3>
                        </div>
                        <i class="fas fa-file-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white mb-0">Départements</h6>
                            <h3 class="text-white mb-0">{{ $reports->pluck('department_id')->unique()->count() }}</h3>
                        </div>
                        <i class="fas fa-building fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection