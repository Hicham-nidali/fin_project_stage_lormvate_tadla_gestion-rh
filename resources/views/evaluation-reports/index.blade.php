@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Rapports d'Évaluation Départementaux</h1>
        <a href="{{ route('evaluation-reports.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau Rapport d'Évaluation
        </a>
    </div>
    
    <div class="card">
        <div class="card-header bg-light">
            <div class="row">
                <div class="col-md-8">
                    <form action="{{ route('evaluation-reports.index') }}" method="GET" class="d-flex">
                        <select name="status" class="form-select me-2" style="width: auto;" onchange="this.form.submit()">
                            <option value="">Tous les statuts</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Envoyé</option>
                            <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Examiné</option>
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
                            <th>Période d'évaluation</th>
                            <th>Créé le</th>
                            <th>Statut</th>
                            <th>Envoyé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                        <tr>
                            <td>{{ $report->title }}</td>
                            <td>{{ $report->evaluation_period_start->format('d/m/Y') }} - {{ $report->evaluation_period_end->format('d/m/Y') }}</td>
                            <td>{{ $report->created_at->format('d/m/Y') }}</td>
                            <td>
                                @if($report->status == 'draft')
                                    <span class="badge bg-secondary">Brouillon</span>
                                @elseif($report->status == 'sent')
                                    <span class="badge bg-primary">Envoyé</span>
                                @else
                                    <span class="badge bg-success">Examiné</span>
                                @endif
                            </td>
                            <td>{{ $report->sent_at ? $report->sent_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                <a href="{{ route('evaluation-reports.show', $report->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($report->status == 'draft')
                                    <a href="{{ route('evaluation-reports.edit', $report->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('evaluation-reports.send', $report->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Envoyer ce rapport à l\'administration RH ?')">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($reports->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Aucun rapport d'évaluation trouvé.
                </div>
            @endif
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Statistiques des rapports</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white mb-0">Brouillons</h6>
                                            <h3 class="text-white mb-0">{{ $reports->where('status', 'draft')->count() }}</h3>
                                        </div>
                                        <i class="fas fa-file-alt fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white mb-0">Envoyés</h6>
                                            <h3 class="text-white mb-0">{{ $reports->where('status', 'sent')->count() }}</h3>
                                        </div>
                                        <i class="fas fa-paper-plane fa-2x opacity-50"></i>
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
                            <div class="card bg-info text-white">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-white mb-0">Total</h6>
                                            <h3 class="text-white mb-0">{{ $reports->count() }}</h3>
                                        </div>
                                        <i class="fas fa-chart-bar fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection