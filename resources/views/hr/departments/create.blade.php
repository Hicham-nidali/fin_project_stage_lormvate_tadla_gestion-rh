@extends('hr.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Créer un Département</h1>
    <a href="{{ route('hr.departments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('hr.departments.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nom du département <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="head_id" class="form-label">Chef de département</label>
                    <select class="form-select @error('head_id') is-invalid @enderror" id="head_id" name="head_id">
                        <option value="">Sélectionner un chef</option>
                        @foreach($availableHeads as $head)
                            <option value="{{ $head->id }}" {{ old('head_id') == $head->id ? 'selected' : '' }}>
                                {{ $head->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('head_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Créer le département</button>
            </div>
        </form>
    </div>
</div>
@endsection