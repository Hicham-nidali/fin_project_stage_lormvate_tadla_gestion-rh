@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-file-alt me-3"></i>{{ $report->title }}</h1>
        <div>
            <a href="{{ route('direction.reports.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button type="button" class="btn btn-info" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimer
            </button>
        </div>
    </div>
    
    <!-- En-tête du rapport -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="mb-1 text-white">{{ $report->title }}</h5>
                    <small class="text-white-50">Rapport Départemental</small>
                </div>
                <div class="col-md-4 text-end">
                    @if($report->status == 'published')
                        <span class="badge bg-light text-success fs-6">Publié</span>
                    @else
                        <span class="badge bg-light text-secondary fs-6">Brouillon</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Département:</strong><br>
                    <span class="badge bg-primary">{{ $report->department->name }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Créé par:</strong> {{ $report->creator->name }}<br>
                    <strong>Date de création:</strong> {{ $report->created_at->format('d/m/Y H:i') }}
                </div>
            </div>
            
            @if($report->period_start && $report->period_end)
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <strong>Période couverte:</strong><br>
                    {{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}
                </div>
                <div class="col-md-6">
                    <strong>Type de rapport:</strong>
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
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Contenu du rapport -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-file-text me-2"></i>Contenu du Rapport</h5>
        </div>
        <div class="card-body report-content">
            {!! nl2br(e($report->content)) !!}
        </div>
    </div>

    <!-- Métadonnées additionnelles -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations Complémentaires</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Détails du Rapport</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>ID du rapport:</span>
                            <strong>#{{ $report->id }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Statut:</span>
                            <strong>{{ ucfirst($report->status) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Type:</span>
                            <strong>{{ ucfirst($report->type) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Dernière modification:</span>
                            <strong>{{ $report->updated_at->format('d/m/Y H:i') }}</strong>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Informations Département</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Département:</span>
                            <strong>{{ $report->department->name }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Chef de département:</span>
                            <strong>{{ $report->department->head ? $report->department->head->name : 'Non assigné' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Créé par:</span>
                            <strong>{{ $report->creator->name }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Email créateur:</span>
                            <strong>{{ $report->creator->email }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Améliorer l'impression
window.addEventListener('beforeprint', function() {
    document.title = '{{ $report->title }} - {{ $report->department->name }}';
});
</script>
@endsection

<style>
.report-content {
    line-height: 1.8;
    font-size: 1rem;
}

@media print {
    .btn, .card-header, .no-print {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .report-content {
        font-size: 12pt;
        line-height: 1.6;
    }
}
</style>