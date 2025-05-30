@extends('hr.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>{{ $department->name }}</h1>
    <div>
        <a href="{{ route('hr.departments.index') }}" class="btn btn-secondary me-2">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        <a href="{{ route('hr.departments.edit', $department->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Modifier
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Informations du département -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Informations du département</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Nom:</dt>
                    <dd class="col-sm-9">{{ $department->name }}</dd>
                    
                    <dt class="col-sm-3">Description:</dt>
                    <dd class="col-sm-9">{{ $department->description ?: 'Aucune description' }}</dd>
                    
                    <dt class="col-sm-3">Chef de département:</dt>
                    <dd class="col-sm-9">
                        @if($department->head)
                            <span class="badge bg-success">{{ $department->head->name }}</span>
                        @else
                            <span class="text-muted">Non assigné</span>
                        @endif
                    </dd>
                    
                    <dt class="col-sm-3">Nombre d'employés:</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-primary">{{ $department->users->count() }}</span>
                    </dd>
                    
                    <dt class="col-sm-3">Date de création:</dt>
                    <dd class="col-sm-9">{{ $department->created_at->format('d/m/Y') }}</dd>
                </dl>
            </div>
        </div>

        <!-- Liste des employés -->
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Employés du département</h5>
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#assignEmployeeModal">
                    <i class="fas fa-plus"></i> Assigner employé
                </button>
            </div>
            <div class="card-body">
                @if($department->users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($department->users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->role == 'department_head')
                                            <span class="badge bg-primary">Chef de département</span>
                                        @else
                                            <span class="badge bg-secondary">Employé</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('hr.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($user->id != $department->head_id)
                                            <form action="{{ route('hr.remove-employee', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Retirer cet employé du département?')">
                                                    <i class="fas fa-user-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>Aucun employé assigné à ce département.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Changer le chef de département -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Gestion du chef</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('hr.departments.update', $department->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ $department->name }}">
                    <input type="hidden" name="description" value="{{ $department->description }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Chef de département</label>
                        <select name="head_id" class="form-select">
                            <option value="">Sélectionner un chef</option>
                            @foreach($availableHeads as $head)
                                <option value="{{ $head->id }}" {{ $department->head_id == $head->id ? 'selected' : '' }}>
                                    {{ $head->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                </form>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Statistiques</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border rounded p-2 mb-2">
                            <h4 class="text-primary mb-0">{{ $department->users->where('role', 'employee')->count() }}</h4>
                            <small class="text-muted">Employés</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 mb-2">
                            <h4 class="text-success mb-0">{{ $department->users->where('role', 'department_head')->count() }}</h4>
                            <small class="text-muted">Chef</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour assigner un employé -->
<div class="modal fade" id="assignEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assigner un employé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hr.assign-employee') }}" method="POST">
                @csrf
                <input type="hidden" name="department_id" value="{{ $department->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employé non assigné</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Sélectionner un employé</option>
                            @foreach($unassignedEmployees as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }} ({{ $user->role == 'employee' ? 'Employé' : 'Chef de département' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Assigner</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection