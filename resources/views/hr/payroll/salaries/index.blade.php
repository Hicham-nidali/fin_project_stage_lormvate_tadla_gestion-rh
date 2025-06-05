@extends('hr.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des Salaires</h1>
        <a href="{{ route('hr.payroll.salaries.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau Salaire
        </a>
    </div>

    @if($employeesWithoutSalary->count() > 0)
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>{{ $employeesWithoutSalary->count() }} employé(s)</strong> sans salaire défini.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Salaires Définis</h6>
                            <h3 class="text-white">{{ $salaries->count() }}</h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Budget Total</h6>
                            <h3 class="text-white">{{ number_format($salaries->sum('base_salary'), 0) }} DH</h3>
                        </div>
                        <i class="fas fa-euro-sign fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Sans Salaire</h6>
                            <h3 class="text-white">{{ $employeesWithoutSalary->count() }}</h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white">Salaire Moyen</h6>
                            <h3 class="text-white">{{ $salaries->count() > 0 ? number_format($salaries->avg('base_salary'), 0) : 0 }} DH</h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
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
                        <div class="col-md-4">
                            <a href="{{ route('hr.payroll.salaries.create') }}" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-plus me-2"></i>Nouveau Salaire
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('hr.payroll.dashboard') }}" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-tachometer-alt me-2"></i>Tableau de Bord
                            </a>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-warning w-100 mb-2" onclick="alert('Fonctionnalité à venir')">
                                <i class="fas fa-file-export me-2"></i>Exporter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des salaires -->
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Salaires Actuels</h5>
            <div>
                <select class="form-select form-select-sm" onchange="filterByDepartment(this.value)">
                    <option value="">Tous les départements</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body">
            @if($salaries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employé</th>
                                <th>Département</th>
                                <th>Salaire de Base</th>
                                <th>Effectif Depuis</th>
                                <th>Effectif Jusqu'à</th>
                                <th>Créé par</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salaries as $salary)
                            <tr data-department="{{ $salary->department_id }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <div class="avatar-title bg-primary rounded-circle">
                                                {{ strtoupper(substr($salary->user->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <strong>{{ $salary->user->name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $salary->department->name }}</td>
                                <td>
                                    <strong class="text-success">{{ number_format($salary->base_salary, 2) }} DH</strong>
                                </td>
                                <td>{{ $salary->effective_from->format('d/m/Y') }}</td>
                                <td>
                                    @if($salary->effective_to)
                                        {{ $salary->effective_to->format('d/m/Y') }}
                                    @else
                                        <span class="badge bg-success">En cours</span>
                                    @endif
                                </td>
                                <td>{{ $salary->creator->name }}</td>
                                <td>
                                    <a href="{{ route('hr.payroll.salaries.edit', $salary->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun salaire défini.</p>
                    <a href="{{ route('hr.payroll.salaries.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Créer le premier salaire
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Employés sans salaire -->
    @if($employeesWithoutSalary->count() > 0)
    <div class="card mt-4">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0">Employés sans salaire défini</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Département</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employeesWithoutSalary as $employee)
                        <tr>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->department->name ?? 'Aucun' }}</td>
                            <td>
                                <a href="{{ route('hr.payroll.salaries.create', ['user_id' => $employee->id]) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus me-1"></i>Définir salaire
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function filterByDepartment(departmentId) {
    const rows = document.querySelectorAll('tbody tr[data-department]');
    
    rows.forEach(row => {
        if (departmentId === '' || row.dataset.department === departmentId) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>
@endsection