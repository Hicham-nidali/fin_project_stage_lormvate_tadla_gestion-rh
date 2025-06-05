@extends('hr.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Nouveau Salaire</h1>
        <a href="{{ route('hr.payroll.salaries.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <form action="{{ route('hr.payroll.salaries.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Employé *</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Sélectionner un employé</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ request('user_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }} - {{ $employee->department->name ?? 'Aucun département' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Salaire de base mensuel (DH) *</label>
                            <input type="number" name="base_salary" class="form-control" step="0.01" min="0" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Effectif à partir du *</label>
                                    <input type="date" name="effective_from" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Effectif jusqu'au</label>
                                    <input type="date" name="effective_to" class="form-control">
                                    <small class="text-muted">Laisser vide pour un salaire permanent</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Créer le salaire
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection