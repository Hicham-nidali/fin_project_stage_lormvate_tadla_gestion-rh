@extends('hr.layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-2 mb-4">Tableau de Bord - Administration RH</h1>
    
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Départements</h6>
                            <h3 class="text-white">{{ $totalDepartments }}</h3>
                        </div>
                        <i class="fas fa-building fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Employés</h6>
                            <h3 class="text-white">{{ $totalEmployees }}</h3>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Chefs</h6>
                            <h3 class="text-white">{{ $totalHeads }}</h3>
                        </div>
                        <i class="fas fa-user-tie fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Non assignés</h6>
                            <h3 class="text-white">{{ $unassignedEmployees }}</h3>
                        </div>
                        <i class="fas fa-user-times fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('hr.departments.create') }}" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-plus me-2"></i>Nouveau Département
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('hr.users.create') }}" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-user-plus me-2"></i>Nouvel Utilisateur
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#assignModal">
                                <i class="fas fa-exchange-alt me-2"></i>Assigner Employé
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-info w-100 mb-2">
                                <i class="fas fa-file-export me-2"></i>Exporter Données
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Départements -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Départements</h5>
                    <a href="{{ route('hr.departments.index') }}" class="btn btn-sm btn-primary">Voir tout</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Département</th>
                                    <th>Chef</th>
                                    <th>Employés</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($departments as $dept)
                                <tr>
                                    <td>
                                        <strong>{{ $dept->name }}</strong>
                                        <br><small class="text-muted">{{ $dept->description }}</small>
                                    </td>
                                    <td>
                                        @if($dept->head)
                                            <span class="badge bg-success">{{ $dept->head->name }}</span>
                                        @else
                                            <span class="badge bg-warning">Non assigné</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $dept->users->count() }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('hr.departments.show', $dept->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('hr.departments.edit', $dept->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal assignation -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assigner un employé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hr.assign-employee') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employé</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Sélectionner</option>
                            @foreach(\App\Models\User::where('role', 'employee')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Département</label>
                        <select name="department_id" class="form-select" required>
                            <option value="">Sélectionner</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
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