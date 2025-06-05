@extends('hr.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Modifier Salaire - {{ $salary->user->name }}</h1>
        <a href="{{ route('hr.payroll.salaries.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <form action="{{ route('hr.payroll.salaries.update', $salary->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Employé</label>
                            <input type="text" class="form-control" value="{{ $salary->user->name }} - {{ $salary->department->name }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Salaire de base mensuel (DH) *</label>
                            <input type="number" name="base_salary" class="form-control" step="0.01" min="0" value="{{ $salary->base_salary }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Effectif à partir du *</label>
                                    <input type="date" name="effective_from" class="form-control" value="{{ $salary->effective_from->format('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Effectif jusqu'au</label>
                                    <input type="date" name="effective_to" class="form-control" value="{{ $salary->effective_to?->format('Y-m-d') }}">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ $salary->notes }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection