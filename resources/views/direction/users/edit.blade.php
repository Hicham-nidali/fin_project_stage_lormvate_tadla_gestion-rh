@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-edit me-2"></i>Modifier Utilisateur</h2>
                <a href="{{ route('direction.users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Modifier : {{ $user->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('direction.users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom complet *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Nouveau mot de passe</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Laisser vide pour conserver le mot de passe actuel</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Rôle *</label>
                                    @if($user->role === 'direction')
                                        <input type="hidden" name="role" value="direction">
                                        <select class="form-select" disabled>
                                            <option selected>Direction (Non modifiable)</option>
                                        </select>
                                        <div class="form-text text-info">
                                            <i class="fas fa-lock me-1"></i>
                                            Les comptes Direction ne peuvent pas être modifiés
                                        </div>
                                    @else
                                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                            <option value="">Sélectionner un rôle</option>
                                            <option value="employee" {{ old('role', $user->role) == 'employee' ? 'selected' : '' }}>
                                                Employé
                                            </option>
                                            <option value="department_head" {{ old('role', $user->role) == 'department_head' ? 'selected' : '' }}>
                                                Chef de Département
                                            </option>
                                            <option value="hr_admin" {{ old('role', $user->role) == 'hr_admin' ? 'selected' : '' }}>
                                                Administrateur RH
                                            </option>
                                        </select>
                                        @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        @if($user->role !== old('role', $user->role))
                                            <div class="form-text text-warning">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Changement de rôle détecté - vérifiez les permissions
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="department_id" class="form-label">Département</label>
                                    <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
                                        <option value="">Aucun département</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" 
                                                {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                                @if($department->head && $department->head->id !== $user->id)
                                                    (Chef: {{ $department->head->name }})
                                                @elseif($department->head && $department->head->id === $user->id)
                                                    (Vous êtes le chef)
                                                @else
                                                    (Sans chef)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text" id="department-help">
                                        @if($user->role == 'department_head' && $user->department)
                                            Actuellement chef du département {{ $user->department->name }}
                                        @else
                                            Assignation de département
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Informations actuelles</label>
                                    <div class="card bg-light">
                                        <div class="card-body py-2">
                                            <small>
                                                <strong>Rôle actuel :</strong> 
                                                @switch($user->role)
                                                    @case('employee') Employé @break
                                                    @case('department_head') Chef de Département @break
                                                    @case('hr_admin') Administrateur RH @break
                                                    @case('direction') Direction @break
                                                    @default {{ $user->role }} @break
                                                @endswitch
                                                <br>
                                                <strong>Département :</strong> 
                                                {{ $user->department ? $user->department->name : 'Aucun' }}
                                                <br>
                                                <strong>Statut :</strong>
                                                @if($user->email_verified_at)
                                                    <span class="text-success">Actif</span>
                                                @else
                                                    <span class="text-danger">Inactif</span>
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($user->role == 'department_head' && $user->headedDepartment)
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Attention :</strong> Cet utilisateur est actuellement chef du département 
                                    <strong>{{ $user->headedDepartment->name }}</strong>. 
                                    Changer son rôle ou son département retirera automatiquement cette responsabilité.
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Alertes de sécurité -->
                        @if($user->id === Auth::id())
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Information :</strong> Vous modifiez votre propre compte. Soyez prudent avec les changements de rôle.
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <div>
                                @if($user->id !== Auth::id() && $user->role !== 'direction')
                                <button type="button" class="btn btn-warning" onclick="toggleUserStatus({{ $user->id }}, '{{ $user->email_verified_at ? 'désactiver' : 'activer' }}')">
                                    <i class="fas fa-{{ $user->email_verified_at ? 'pause' : 'play' }} me-2"></i>
                                    {{ $user->email_verified_at ? 'Désactiver' : 'Activer' }} le compte
                                </button>
                                @endif
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="{{ route('direction.users.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </a>
                                @if($user->role !== 'direction')
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form pour toggle status -->
<form id="toggleStatusForm" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const departmentSelect = document.getElementById('department_id');
    const departmentHelp = document.getElementById('department-help');
    
    function updateDepartmentField() {
        if (!roleSelect) return; // Si c'est un compte direction, pas de roleSelect
        
        const selectedRole = roleSelect.value;
        
        switch(selectedRole) {
            case 'department_head':
                departmentHelp.textContent = 'Sera assigné comme chef du département sélectionné';
                departmentHelp.className = 'form-text text-warning';
                break;
            case 'hr_admin':
                departmentHelp.textContent = 'Optionnel - les admins RH travaillent transversalement';
                departmentHelp.className = 'form-text text-info';
                break;
            default:
                departmentHelp.textContent = 'Assignation de département';
                departmentHelp.className = 'form-text';
        }
    }
    
    if (roleSelect) {
        roleSelect.addEventListener('change', updateDepartmentField);
    }
});

function toggleUserStatus(userId, action) {
    if (confirm(`Êtes-vous sûr de vouloir ${action} ce compte utilisateur ?`)) {
        const form = document.getElementById('toggleStatusForm');
        form.action = `/direction/users/${userId}/toggle-status`;
        form.submit();
    }
}
</script>
@endsection