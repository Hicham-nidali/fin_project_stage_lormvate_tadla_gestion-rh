@extends('hr.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Gestion des Départements</h1>
    <a href="{{ route('hr.departments.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau Département
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Chef</th>
                        <th>Employés</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departments as $dept)
                    <tr>
                        <td><strong>{{ $dept->name }}</strong></td>
                        <td>{{ $dept->description }}</td>
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
                            <form action="{{ route('hr.departments.destroy', $dept->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Confirmer la suppression?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection