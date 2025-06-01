@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Créer un Rapport d'Évaluation</h1>
        <a href="{{ route('evaluation-reports.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('evaluation-reports.store') }}" method="POST">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="title" class="form-label">Titre du rapport <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', 'Rapport d\'évaluation - ' . $defaultStart->format('M Y')) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="evaluation_period_start" class="form-label">Début de la période d'évaluation <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('evaluation_period_start') is-invalid @enderror" id="evaluation_period_start" name="evaluation_period_start" value="{{ old('evaluation_period_start', $defaultStart->format('Y-m-d')) }}" required>
                        @error('evaluation_period_start')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="evaluation_period_end" class="form-label">Fin de la période d'évaluation <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('evaluation_period_end') is-invalid @enderror" id="evaluation_period_end" name="evaluation_period_end" value="{{ old('evaluation_period_end', $defaultEnd->format('Y-m-d')) }}" required>
                        @error('evaluation_period_end')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="summary" class="form-label">Résumé exécutif <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('summary') is-invalid @enderror" id="summary" name="summary" rows="5" required placeholder="Résumez les principales observations et conclusions de cette période d'évaluation...">{{ old('summary') }}</textarea>
                    @error('summary')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Ce résumé sera inclus dans le rapport final. Décrivez les performances générales, les points positifs et les domaines d'amélioration.</div>
                </div>
                
                <div class="mb-3">
                    <label for="recommendations" class="form-label">Recommandations</label>
                    <textarea class="form-control @error('recommendations') is-invalid @enderror" id="recommendations" name="recommendations" rows="4" placeholder="Vos recommandations pour améliorer les performances de l'équipe...">{{ old('recommendations') }}</textarea>
                    @error('recommendations')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Proposez des actions concrètes pour améliorer les performances individuelles et d'équipe.</div>
                </div>
                
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Informations importantes</h6>
                    <ul class="mb-0">
                        <li>Les données de pointage, tâches et demandes seront automatiquement collectées pour la période sélectionnée</li>
                        <li>Les scores de performance seront calculés automatiquement pour chaque employé</li>
                        <li>Vous pourrez modifier ce rapport tant qu'il n'est pas envoyé à l'administration RH</li>
                        <li>Une fois envoyé, le rapport ne pourra plus être modifié</li>
                    </ul>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Créer le rapport (Brouillon)
                    </button>
                    <a href="{{ route('evaluation-reports.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
    // Validation des dates
    document.getElementById('evaluation_period_start').addEventListener('change', function() {
        const startDate = new Date(this.value);
        const endDateInput = document.getElementById('evaluation_period_end');
        const endDate = new Date(endDateInput.value);
        
        if (endDate <= startDate) {
            const newEndDate = new Date(startDate);
            newEndDate.setDate(newEndDate.getDate() + 1);
            endDateInput.value = newEndDate.toISOString().split('T')[0];
        }
        
        // Mettre à jour le titre automatiquement
        const titleInput = document.getElementById('title');
        const monthNames = ["Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil", "Aoû", "Sep", "Oct", "Nov", "Déc"];
        const month = monthNames[startDate.getMonth()];
        const year = startDate.getFullYear();
        titleInput.value = `Rapport d'évaluation - ${month} ${year}`;
    });
</script>
@endsection
@endsection