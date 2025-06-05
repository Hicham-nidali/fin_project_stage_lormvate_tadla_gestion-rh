@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-edit me-3"></i>Modifier l'Objectif</h1>
        <div>
            <a href="{{ route('direction.objectives.show', $objective->id) }}" class="btn btn-info me-2">
                <i class="fas fa-eye me-2"></i>Voir
            </a>
            <a href="{{ route('direction.objectives.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('direction.objectives.update', $objective->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="title" class="form-label">Titre de l'objectif <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $objective->title) }}" required>
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
                                <option value="{{ $dept->id }}" 
                                    {{ old('department_id', $objective->department_id) == $dept->id ? 'selected' : '' }}>
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
                              id="description" name="description" rows="4" required>{{ old('description', $objective->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="type" class="form-label">Type d'objectif <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" name="type" required>
                            <option value="monthly" {{ old('type', $objective->type) == 'monthly' ? 'selected' : '' }}>Mensuel</option>
                            <option value="quarterly" {{ old('type', $objective->type) == 'quarterly' ? 'selected' : '' }}>Trimestriel</option>
                            <option value="annual" {{ old('type', $objective->type) == 'annual' ? 'selected' : '' }}>Annuel</option>
                            <option value="custom" {{ old('type', $objective->type) == 'custom' ? 'selected' : '' }}>Personnalisé</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-3">
                        <label for="priority" class="form-label">Priorité <span class="text-danger">*</span></label>
                        <select class="form-select @error('priority') is-invalid @enderror" 
                                id="priority" name="priority" required>
                            <option value="low" {{ old('priority', $objective->priority) == 'low' ? 'selected' : '' }}>Faible</option>
                            <option value="medium" {{ old('priority', $objective->priority) == 'medium' ? 'selected' : '' }}>Moyenne</option>
                            <option value="high" {{ old('priority', $objective->priority) == 'high' ? 'selected' : '' }}>Haute</option>
                            <option value="critical" {{ old('priority', $objective->priority) == 'critical' ? 'selected' : '' }}>Critique</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Date de début <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                               id="start_date" name="start_date" 
                               value="{{ old('start_date', $objective->start_date->format('Y-m-d')) }}" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-3">
                        <label for="due_date" class="form-label">Date d'échéance <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                               id="due_date" name="due_date" 
                               value="{{ old('due_date', $objective->due_date->format('Y-m-d')) }}" required>
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Métriques existantes -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Métriques et indicateurs de performance (KPIs)</label>
                        <div id="metrics-container">
                            @if($objective->metrics && count($objective->metrics) > 0)
                                @foreach($objective->metrics as $index => $metric)
                                <div class="metric-row row mb-2">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="metrics[{{ $index }}][name]" 
                                               placeholder="Nom de la métrique" 
                                               value="{{ old('metrics.'.$index.'.name', $metric['name'] ?? '') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" class="form-control" name="metrics[{{ $index }}][target]" 
                                               placeholder="Objectif cible" 
                                               value="{{ old('metrics.'.$index.'.target', $metric['target'] ?? '') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control" name="metrics[{{ $index }}][unit]" 
                                               placeholder="Unité (%, €, nombre...)" 
                                               value="{{ old('metrics.'.$index.'.unit', $metric['unit'] ?? '') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger remove-metric">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="metric-row row mb-2">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" name="metrics[0][name]" 
                                               placeholder="Nom de la métrique">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" class="form-control" name="metrics[0][target]" 
                                               placeholder="Objectif cible">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control" name="metrics[0][unit]" 
                                               placeholder="Unité (%, €, nombre...)">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger remove-metric">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-metric">
                            <i class="fas fa-plus me-2"></i>Ajouter une métrique
                        </button>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes et instructions</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                              id="notes" name="notes" rows="3">{{ old('notes', $objective->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_critical" name="is_critical" 
                               value="1" {{ old('is_critical', $objective->is_critical) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_critical">
                            <i class="fas fa-fire text-danger me-2"></i>Marquer comme objectif critique
                        </label>
                    </div>
                </div>
                
                @if($objective->status !== 'completed')
                <div class="alert alert-warning">
                    <h6><i class="fas fa-info-circle me-2"></i>Information importante</h6>
                    <ul class="mb-0">
                        <li>Si vous changez le département, une nouvelle notification sera envoyée</li>
                        <li>Les modifications seront visibles immédiatement pour le chef de département</li>
                        <li>L'historique des modifications sera conservé</li>
                    </ul>
                </div>
                @endif
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('direction.objectives.show', $objective->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Sauvegarder les modifications
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
    let metricIndex = {{ $objective->metrics ? count($objective->metrics) : 1 }};
    
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
});
</script>
@endsection