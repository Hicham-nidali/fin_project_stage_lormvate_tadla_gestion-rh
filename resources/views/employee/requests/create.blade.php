@extends('employee.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Nouvelle Demande</h1>
        <a href="{{ route('employee.requests.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('employee.requests.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="title" class="form-label">Titre de la demande <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="type" class="form-label">Type de demande <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">Sélectionner un type</option>
                            <option value="leave" {{ old('type') == 'leave' ? 'selected' : '' }}>Congé</option>
                            <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Remboursement</option>
                            <option value="equipment" {{ old('type') == 'equipment' ? 'selected' : '' }}>Équipement</option>
                            <option value="overtime" {{ old('type') == 'overtime' ? 'selected' : '' }}>Heures supplémentaires</option>
                            <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Autre</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Champs spécifiques aux heures supplémentaires -->
                <div id="overtime-fields" class="request-type-fields" style="display: none;">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Remplissez les détails de votre demande d'heures supplémentaires
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="overtime_date" class="form-label">Date des heures supplémentaires</label>
                            <input type="date" class="form-control @error('overtime_date') is-invalid @enderror" id="overtime_date" name="overtime_date" value="{{ old('overtime_date') }}">
                            @error('overtime_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="overtime_type" class="form-label">Type d'heures supplémentaires</label>
                            <select class="form-select @error('overtime_type') is-invalid @enderror" id="overtime_type" name="overtime_type">
                                <option value="planned" {{ old('overtime_type') == 'planned' ? 'selected' : '' }}>Planifiées</option>
                                <option value="urgent" {{ old('overtime_type') == 'urgent' ? 'selected' : '' }}>Urgentes</option>
                                <option value="project" {{ old('overtime_type') == 'project' ? 'selected' : '' }}>Projet spécial</option>
                            </select>
                            @error('overtime_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="start_time" class="form-label">Heure de début</label>
                            <input type="time" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time') }}">
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="end_time" class="form-label">Heure de fin</label>
                            <input type="time" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time') }}">
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="hours_requested" class="form-label">Heures demandées</label>
                            <input type="number" step="0.5" min="0.5" max="12" class="form-control @error('hours_requested') is-invalid @enderror" id="hours_requested" name="hours_requested" value="{{ old('hours_requested') }}" readonly>
                            @error('hours_requested')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="overtime_rate" class="form-label">Taux</label>
                            <select class="form-select @error('overtime_rate') is-invalid @enderror" id="overtime_rate" name="overtime_rate">
                                <option value="1.25" {{ old('overtime_rate') == '1.25' ? 'selected' : '' }}>125% (Standard)</option>
                                <option value="1.5" {{ old('overtime_rate') == '1.5' ? 'selected' : '' }}>150% (Nuit/Weekend)</option>
                                <option value="2" {{ old('overtime_rate') == '2' ? 'selected' : '' }}>200% (Jours fériés)</option>
                            </select>
                            @error('overtime_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="overtime_reason" class="form-label">Raison des heures supplémentaires</label>
                            <textarea class="form-control @error('overtime_reason') is-invalid @enderror" id="overtime_reason" name="overtime_reason" rows="3">{{ old('overtime_reason') }}</textarea>
                            @error('overtime_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Champs spécifiques au congé -->
                <div id="leave-fields" class="request-type-fields" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="leave_start_date" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="leave_start_date" name="leave_start_date" value="{{ old('leave_start_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="leave_end_date" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="leave_end_date" name="leave_end_date" value="{{ old('leave_end_date') }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="leave_type" class="form-label">Type de congé</label>
                            <select class="form-select" id="leave_type" name="leave_type">
                                <option value="annual" {{ old('leave_type') == 'annual' ? 'selected' : '' }}>Congé annuel</option>
                                <option value="sick" {{ old('leave_type') == 'sick' ? 'selected' : '' }}>Congé maladie</option>
                                <option value="personal" {{ old('leave_type') == 'personal' ? 'selected' : '' }}>Congé personnel</option>
                                <option value="other" {{ old('leave_type') == 'other' ? 'selected' : '' }}>Autre</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Champs spécifiques aux dépenses -->
                <div id="expense-fields" class="request-type-fields" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="expense_amount" class="form-label">Montant</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="expense_amount" name="expense_amount" value="{{ old('expense_amount') }}">
                                <span class="input-group-text">€</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="expense_date" class="form-label">Date de la dépense</label>
                            <input type="date" class="form-control" id="expense_date" name="expense_date" value="{{ old('expense_date') }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="expense_category" class="form-label">Catégorie</label>
                            <select class="form-select" id="expense_category" name="expense_category">
                                <option value="travel" {{ old('expense_category') == 'travel' ? 'selected' : '' }}>Déplacement</option>
                                <option value="meal" {{ old('expense_category') == 'meal' ? 'selected' : '' }}>Repas</option>
                                <option value="equipment" {{ old('expense_category') == 'equipment' ? 'selected' : '' }}>Équipement</option>
                                <option value="other" {{ old('expense_category') == 'other' ? 'selected' : '' }}>Autre</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="expense_receipt" class="form-label">Justificatif</label>
                            <input type="file" class="form-control" id="expense_receipt" name="expense_receipt">
                        </div>
                    </div>
                </div>
                
                <!-- Champs spécifiques à l'équipement -->
                <div id="equipment-fields" class="request-type-fields" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="equipment_type" class="form-label">Type d'équipement</label>
                            <select class="form-select" id="equipment_type" name="equipment_type">
                                <option value="computer" {{ old('equipment_type') == 'computer' ? 'selected' : '' }}>Ordinateur</option>
                                <option value="phone" {{ old('equipment_type') == 'phone' ? 'selected' : '' }}>Téléphone</option>
                                <option value="office" {{ old('equipment_type') == 'office' ? 'selected' : '' }}>Fournitures de bureau</option>
                                <option value="other" {{ old('equipment_type') == 'other' ? 'selected' : '' }}>Autre</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="equipment_urgency" class="form-label">Niveau d'urgence</label>
                            <select class="form-select" id="equipment_urgency" name="equipment_urgency">
                                <option value="low" {{ old('equipment_urgency') == 'low' ? 'selected' : '' }}>Faible</option>
                                <option value="medium" {{ old('equipment_urgency') == 'medium' ? 'selected' : '' }}>Moyen</option>
                                <option value="high" {{ old('equipment_urgency') == 'high' ? 'selected' : '' }}>Élevé</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="attachment" class="form-label">Pièce jointe (facultatif)</label>
                    <input type="file" class="form-control" id="attachment" name="attachment">
                    <small class="form-text text-muted">Taille maximum: 5 MB. Formats acceptés: PDF, DOC, DOCX, JPG, PNG.</small>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Soumettre la demande</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Afficher/masquer les champs en fonction du type de demande
    document.getElementById('type').addEventListener('change', function() {
        // Masquer tous les champs spécifiques
        document.querySelectorAll('.request-type-fields').forEach(function(element) {
            element.style.display = 'none';
        });
        
        // Afficher les champs correspondant au type sélectionné
        const selectedType = this.value;
        if(selectedType) {
            const fieldsElement = document.getElementById(selectedType + '-fields');
            if(fieldsElement) {
                fieldsElement.style.display = 'block';
            }
        }
    });
    
    // Calcul automatique des heures pour les heures supplémentaires
    function calculateOvertimeHours() {
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;
        
        if (startTime && endTime) {
            const start = new Date('2000-01-01 ' + startTime);
            const end = new Date('2000-01-01 ' + endTime);
            
            if (end > start) {
                const diffMs = end - start;
                const diffHours = diffMs / (1000 * 60 * 60);
                document.getElementById('hours_requested').value = diffHours.toFixed(1);
            }
        }
    }
    
    document.getElementById('start_time').addEventListener('change', calculateOvertimeHours);
    document.getElementById('end_time').addEventListener('change', calculateOvertimeHours);
    
    // Initialiser l'affichage en fonction de la valeur déjà sélectionnée (utile en cas d'erreur de validation)
    const initialType = document.getElementById('type').value;
    if(initialType) {
        const fieldsElement = document.getElementById(initialType + '-fields');
        if(fieldsElement) {
            fieldsElement.style.display = 'block';
        }
    }
</script>
@endsection