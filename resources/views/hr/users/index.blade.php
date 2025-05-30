@extends('hr.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Gestion des Utilisateurs</h1>
    <a href="{{ route('hr.users.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus me-2"></i>Nouvel Utilisateur
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Département</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->role == 'hr_admin')
                                <span class="badge bg-warning">Admin RH</span>
                            @elseif($user->role == 'department_head')
                                <span class="badge bg-primary">Chef de Dept.</span>
                            @else
                                <span class="badge bg-secondary">Employé</span>
                            @endif
                        </td>
                        <td>
                            @if($user->department)
                                {{ $user->department->name }}
                            @else
                                <span class="text-muted">Non assigné</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('hr.users.edit', $user->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($user->department_id)
                                <form action="{{ route('hr.remove-employee', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" title="Retirer du département">
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
    </div>
</div>
@endsection