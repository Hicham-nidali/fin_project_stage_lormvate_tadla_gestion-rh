@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-bullseye me-3"></i>Créer un Nouvel Objectif</h1>
        <a href="{{ route('direction.objectives.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('direction.objectives.store') }}" method="POST">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="title" class="form-label">Titre de l'objectif <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4">
                        <label for="department_id" class="form-label">Département <span class="text-danger">*</span></label>
                        <select class="form-select @error('department_id') is-invalid @enderror" 
                                id="department_id" name="department_id" required>
                            <option value="">Sélectionner un département</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }} 
                                    @if($dept->head)
                                        ({{ $dept->head->name }})
                                    @else
                                        (Sans chef)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description détaillée <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Décrivez clairement l'objectif, les résultats attendus et les critères de réussite.</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="type" class="form-label">Type d'objectif <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" name="type" required>
                            <option value="monthly" {{ old('type', 'monthly') == 'monthly' ? 'selected' : '' }}>Mensuel</option>
                            <option value="quarterly" {{ old('type') == 'quarterly' ? 'selected' : '' }}>Trimestriel</option>
                            <option value="annual" {{ old('type') == 'annual' ? 'selected' : '' }}>Annuel</option>
                            <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>Personnalisé</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-3">
                        <label for="priority" class="form-label">Priorité <span class="text-danger">*</span></label>
                        <select class="form-select @error('priority') is-invalid @enderror" 
                                id="priority" name="priority" required>
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Faible</option>
                            <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Moyenne</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Haute</option>
                            <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critique</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Date de début <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                               id="start_date" name="start_date" 
                               value="{{ old('start_date', $defaultStart->format('Y-m-d')) }}" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-3">
                        <label for="due_date" class="form-label">Date d'échéance <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                               id="due_date" name="due_date" 
                               value="{{ old('due_date', $defaultEnd->format('Y-m-d')) }}" required>
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Métriques et KPIs -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Métriques et indicateurs de performance (KPIs)</label>
                        <div id="metrics-container">
                            <div class="metric-row row mb-2">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="metrics[0][name]" 
                                           placeholder="Nom de la métrique" value="{{ old('metrics.0.name') }}">
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" name="metrics[0][target]" 
                                           placeholder="Objectif cible" value="{{ old('metrics.0.target') }}">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="metrics[0][unit]" 
                                           placeholder="Unité (%, €, nombre...)" value="{{ old('metrics.0.unit') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger remove-metric">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-metric">
                            <i class="fas fa-plus me-2"></i>Ajouter une métrique
                        </button>
                        <div class="form-text">Définissez des indicateurs mesurables pour évaluer la réussite de l'objectif.</div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes et instructions</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                              id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Instructions spéciales, contexte ou informations complémentaires.</div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_critical" name="is_critical" 
                               value="1" {{ old('is_critical') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_critical">
                            <i class="fas fa-fire text-danger me-2"></i>Marquer comme objectif critique
                        </label>
                        <div class="form-text">Les objectifs critiques sont prioritaires et nécessitent un suivi renforcé.</div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Information importante</h6>
                    <ul class="mb-0">
                        <li>Une notification sera automatiquement envoyée au chef du département sélectionné</li>
                        <li>L'objectif sera visible immédiatement dans l'interface du chef de département</li>
                        <li>Le suivi de progression pourra être effectué en temps réel</li>
                        <li>Vous pourrez modifier cet objectif tant qu'il n'est pas terminé</li>
                    </ul>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('direction.objectives.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-bullseye me-2"></i>Créer et Assigner l'Objectif
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let metricIndex = 1;
    
    // Ajouter une nouvelle métrique
    document.getElementById('add-metric').addEventListener('click', function() {
        const container = document.getElementById('metrics-container');
        const newRow = document.createElement('div');
        newRow.className = 'metric-row row mb-2';
        newRow.innerHTML = `
            <div class="col-md-4">
                <input type="text" class="form-control" name="metrics[${metricIndex}][name]" 
                       placeholder="Nom de la métrique">
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control" name="metrics[${metricIndex}][target]" 
                       placeholder="Objectif cible">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="metrics[${metricIndex}][unit]" 
                       placeholder="Unité (%, €, nombre...)">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger remove-metric">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(newRow);
        metricIndex++;
    });
    
    // Supprimer une métrique
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-metric')) {
            const row = e.target.closest('.metric-row');
            if (document.querySelectorAll('.metric-row').length > 1) {
                row.remove();
            } else {
                alert('Vous devez garder au moins une métrique.');
            }
        }
    });
    
    // Validation des dates
    document.getElementById('start_date').addEventListener('change', function() {
        const startDate = new Date(this.value);
        const endDateInput = document.getElementById('due_date');
        const endDate = new Date(endDateInput.value);
        
        if (endDate <= startDate) {
            const newEndDate = new Date(startDate);
            newEndDate.setDate(newEndDate.getDate() + 1);
            endDateInput.value = newEndDate.toISOString().split('T')[0];
        }
    });
    
    // Ajustement automatique des dates selon le type
    document.getElementById('type').addEventListener('change', function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('due_date');
        const startDate = new Date(startDateInput.value);
        
        switch(this.value) {
            case 'monthly':
                const endOfMonth = new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0);
                endDateInput.value = endOfMonth.toISOString().split('T')[0];
                break;
            case 'quarterly':
                const endOfQuarter = new Date(startDate);
                endOfQuarter.setMonth(endOfQuarter.getMonth() + 3);
                endDateInput.value = endOfQuarter.toISOString().split('T')[0];
                break;
            case 'annual':
                const endOfYear = new Date(startDate.getFullYear(), 11, 31);
                endDateInput.value = endOfYear.toISOString().split('T')[0];
                break;
        }
    });
    
    // Preview de l'objectif
    const inputs = ['title', 'description', 'type', 'priority'];
    inputs.forEach(id => {
        document.getElementById(id).addEventListener('input', updatePreview);
    });
    
    function updatePreview() {
        // Ici vous pourriez ajouter une preview en temps réel de l'objectif
    }
});
</script>
@endsection