<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Direction - Consultation Présences</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <div class="bg-dark" id="sidebar-wrapper">
            <div class="sidebar-heading text-white text-center py-4">
                <h4><i class="fas fa-crown me-2"></i>Direction</h4>
                <small class="text-white-50">Consultation Présences</small>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('direction.dashboard') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('direction.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                </a>
                
                <a href="{{ route('direction.attendance') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('direction.attendance*') ? 'active' : '' }}">
                    <i class="fas fa-users-clock me-2"></i>Présences Globales
                </a>
                
                <a href="{{ route('direction.objectives.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('direction.objectives*') ? 'active' : '' }}">
                    <i class="fas fa-bullseye me-2"></i>Objectifs Départementaux
                    @php
                        $overdueObjectives = \App\Models\Objective::overdue()->count();
                        $criticalObjectives = \App\Models\Objective::critical()->active()->count();
                        $totalAlerts = $overdueObjectives + $criticalObjectives;
                    @endphp
                    @if($totalAlerts > 0)
                        <span class="badge bg-warning rounded-pill float-end notification-badge">{{ $totalAlerts }}</span>
                    @endif
                </a>
            </div>
        </div>
        
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-dark" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="ms-auto">
                        <div class="dropdown">
                            <button class="btn btn-dark dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-crown me-2"></i>{{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    @yield('scripts')
</body>
</html>