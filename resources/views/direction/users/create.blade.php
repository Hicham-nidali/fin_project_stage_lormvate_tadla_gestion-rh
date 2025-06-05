@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-plus me-2"></i>Nouvel Utilisateur</h2>
                <a href="{{ route('direction.users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Créer un nouvel utilisateur</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('direction.users.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom complet *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mot de passe *</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Minimum 6 caractères</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Rôle *</label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                        <option value="">Sélectionner un rôle</option>
                                        <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>
                                            <i class="fas fa-user"></i> Employé
                                        </option>
                                        <option value="department_head" {{ old('role') == 'department_head' ? 'selected' : '' }}>
                                            <i class="fas fa-user-tie"></i> Chef de Département
                                        </option>
                                        <option value="hr_admin" {{ old('role') == 'hr_admin' ? 'selected' : '' }}>
                                            <i class="fas fa-user-shield"></i> Administrateur RH
                                        </option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="department_id" class="form-label">Département</label>
                                    <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
                                        <option value="">Sélectionner un département</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" 
                                                {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                                @if($department->head)
                                                    (Chef: {{ $department->head->name }})
                                                @else
                                                    (Sans chef)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text" id="department-help">Optionnel - peut être assigné plus tard</div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations sur les rôles -->
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Description des rôles :</h6>
                                    <ul class="mb-0">
                                        <li><strong>Employé :</strong> Accès aux fonctionnalités de base (pointage, demandes, tâches)</li>
                                        <li><strong>Chef de Département :</strong> Gestion d'équipe + fonctionnalités employé</li>
                                        <li><strong>Administrateur RH :</strong> Gestion complète des ressources humaines</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('direction.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Créer l'utilisateur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const departmentSelect = document.getElementById('department_id');
    const departmentHelp = document.getElementById('department-help');
    
    function updateDepartmentField() {
        const selectedRole = roleSelect.value;
        
        switch(selectedRole) {
            case 'department_head':
                departmentHelp.textContent = 'Recommandé - sera assigné comme chef du département sélectionné';
                departmentHelp.className = 'form-text text-warning';
                break;
            case 'hr_admin':
                departmentHelp.textContent = 'Optionnel - les admins RH travaillent transversalement';
                departmentHelp.className = 'form-text text-info';
                break;
            default:
                departmentHelp.textContent = 'Optionnel - peut être assigné plus tard';
                departmentHelp.className = 'form-text';
        }
    }
    
    roleSelect.addEventListener('change', updateDepartmentField);
    updateDepartmentField(); // Init
});
</script>
@endsection