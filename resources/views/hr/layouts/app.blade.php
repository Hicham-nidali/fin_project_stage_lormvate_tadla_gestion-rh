<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration RH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-warning" id="sidebar-wrapper">
            <div class="sidebar-heading text-dark text-center py-4">
                <h4><i class="fas fa-users-cog me-2"></i>Administration RH</h4>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('hr.dashboard') }}" class="list-group-item list-group-item-action bg-transparent text-dark {{ request()->routeIs('hr.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                </a>
                <a href="{{ route('hr.attendance.index') }}" class="list-group-item list-group-item-action bg-transparent text-dark {{ request()->routeIs('hr.attendance*') ? 'active' : '' }}">
                    <i class="fas fa-user-clock me-2"></i>Mon Pointage
                </a>
                <a href="{{ route('hr.departments.index') }}" class="list-group-item list-group-item-action bg-transparent text-dark {{ request()->routeIs('hr.departments*') ? 'active' : '' }}">
                    <i class="fas fa-building me-2"></i>Départements
                </a>
                <a href="{{ route('hr.users.index') }}" class="list-group-item list-group-item-action bg-transparent text-dark {{ request()->routeIs('hr.users*') ? 'active' : '' }}">
                    <i class="fas fa-users me-2"></i>Utilisateurs
                </a>
            </div>
        </div>
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-warning" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="ms-auto">
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-user-shield me-2"></i>{{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i>Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    @yield('scripts')
    <script>
// Heartbeat pour maintenir la session active
setInterval(function() {
    fetch('/heartbeat', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin'
    }).catch(function() {
        console.log('Session expirée');
    });
}, 300000); // Toutes les 5 minutes
</script>
</body>
</html>