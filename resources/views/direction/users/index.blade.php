@extends('layouts.direction')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-users-cog me-2"></i>Gestion des Utilisateurs</h2>
                <a href="{{ route('direction.users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouvel Utilisateur
                </a>
            </div>

            <!-- Statistiques rapides -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3>{{ $totalUsers }}</h3>
                            <small>Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3>{{ $totalEmployees }}</h3>
                            <small>Employés</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h3>{{ $totalHeads }}</h3>
                            <small>Chefs</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3>{{ $totalHR }}</h3>
                            <small>RH</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-dark text-white">
                        <div class="card-body text-center">
                            <h3>{{ $totalDirection }}</h3>
                            <small>Direction</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h3>{{ $unassignedUsers }}</h3>
                            <small>Sans Dépt.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres et recherche -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" id="search-users" class="form-control" placeholder="Rechercher un utilisateur...">
                        </div>
                        <div class="col-md-3">
                            <select id="filter-role" class="form-select">
                                <option value="">Tous les rôles</option>
                                <option value="employee">Employés</option>
                                <option value="department_head">Chefs de Département</option>
                                <option value="hr_admin">Admin RH</option>
                                <option value="direction">Direction</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="filter-department" class="form-select">
                                <option value="">Tous les départements</option>
                                <option value="unassigned">Sans département</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table des utilisateurs -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Liste des Utilisateurs ({{ $totalUsers }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="users-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>UTILISATEUR</th>
                                    <th>EMAIL</th>
                                    <th>RÔLE</th>
                                    <th>DÉPARTEMENT</th>
                                    <th>STATUT</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr data-role="{{ $user->role }}" data-department="{{ $user->department_id ?? 'unassigned' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3 bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <strong>{{ $user->name }}</strong>
                                                @if($user->id === Auth::id())
                                                    <span class="badge bg-info ms-1">Vous</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @switch($user->role)
                                            @case('employee')
                                                <span class="badge bg-success">Employé</span>
                                                @break
                                            @case('department_head')
                                                <span class="badge bg-warning">Chef de Département</span>
                                                @break
                                            @case('hr_admin')
                                                <span class="badge bg-info">Administrateur RH</span>
                                                @break
                                            @case('direction')
                                                <span class="badge bg-dark">Direction</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $user->role }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($user->department)
                                            <span class="badge bg-primary">{{ $user->department->name }}</span>
                                        @else
                                            <span class="text-muted">Aucun</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($user->role !== 'direction')
                                            <a href="{{ route('direction.users.edit', $user->id) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                            
                                            @if($user->id !== Auth::id() && $user->role !== 'direction')
                                            <form method="POST" action="{{ route('direction.users.toggle-status', $user->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-{{ $user->email_verified_at ? 'warning' : 'success' }}" 
                                                        title="{{ $user->email_verified_at ? 'Désactiver' : 'Activer' }}">
                                                    <i class="fas fa-{{ $user->email_verified_at ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Supprimer"
                                                    onclick="confirmDelete('{{ $user->name }}', '{{ route('direction.users.destroy', $user->id) }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-users fa-3x mb-3"></i>
                                            <p>Aucun utilisateur trouvé</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer l'utilisateur <strong id="deleteUserName"></strong> ?</p>
                <p class="text-danger"><small>Cette action est irréversible.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-users');
    const roleFilter = document.getElementById('filter-role');
    const departmentFilter = document.getElementById('filter-department');
    const table = document.getElementById('users-table');
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedRole = roleFilter.value;
        const selectedDepartment = departmentFilter.value;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const role = row.dataset.role;
            const department = row.dataset.department;

            const matchesSearch = text.includes(searchTerm);
            const matchesRole = !selectedRole || role === selectedRole;
            const matchesDepartment = !selectedDepartment || department === selectedDepartment;

            row.style.display = matchesSearch && matchesRole && matchesDepartment ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterTable);
    roleFilter.addEventListener('change', filterTable);
    departmentFilter.addEventListener('change', filterTable);
});

function confirmDelete(userName, deleteUrl) {
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteForm').action = deleteUrl;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<style>
.avatar-sm {
    width: 35px;
    height: 35px;
}

.notification-badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
</style>
@endsection