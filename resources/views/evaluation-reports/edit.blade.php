@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Modifier le Rapport d'Évaluation</h1>
        <div>
            <a href="{{ route('evaluation-reports.show', $report->id) }}" class="btn btn-info me-2">
                <i class="fas fa-eye"></i> Aperçu
            </a>
            <a href="{{ route('evaluation-reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('evaluation-reports.update', $report->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="title" class="form-label">Titre du rapport <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $report->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="evaluation_period_start" class="form-label">Début de la période d'évaluation <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('evaluation_period_start') is-invalid @enderror" id="evaluation_period_start" name="evaluation_period_start" value="{{ old('evaluation_period_start', $report->evaluation_period_start->format('Y-m-d')) }}" required>
                        @error('evaluation_period_start')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="evaluation_period_end" class="form-label">Fin de la période d'évaluation <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('evaluation_period_end') is-invalid @enderror" id="evaluation_period_end" name="evaluation_period_end" value="{{ old('evaluation_period_end', $report->evaluation_period_end->format('Y-m-d')) }}" required>
                        @error('evaluation_period_end')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="summary" class="form-label">Résumé exécutif <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('summary') is-invalid @enderror" id="summary" name="summary" rows="5" required>{{ old('summary', $report->summary) }}</textarea>
                    @error('summary')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="recommendations" class="form-label">Recommandations</label>
                    <textarea class="form-control @error('recommendations') is-invalid @enderror" id="recommendations" name="recommendations" rows="4">{{ old('recommendations', $report->recommendations) }}</textarea>
                    @error('recommendations')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Attention</h6>
                    <p class="mb-0">Si vous modifiez les dates de la période d'évaluation, toutes les données (pointage, tâches, demandes) seront recalculées automatiquement pour la nouvelle période.</p>
                </div>
                
                <div class="d-flex justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-save me-2"></i>Sauvegarder les modifications
                        </button>
                        <a href="{{ route('evaluation-reports.show', $report->id) }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                    <form method="POST" action="{{ route('evaluation-reports.send', $report->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Sauvegarder et envoyer ce rapport à l\'administration RH ?')">
                            <i class="fas fa-paper-plane me-2"></i>Sauvegarder et Envoyer
                        </button>
                    </form>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection