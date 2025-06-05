<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Système de Gestion - Administration RH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-dark" id="sidebar-wrapper">
            <div class="sidebar-heading text-white text-center py-4">
                <h4>Administration RH</h4>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('hr.dashboard') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('hr.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                </a>
                
                <a href="{{ route('hr.departments.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('hr.departments*') ? 'active' : '' }}">
                    <i class="fas fa-building me-2"></i>Départements
                </a>
                
                <a href="{{ route('hr.users.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('hr.users*') ? 'active' : '' }}">
                    <i class="fas fa-users me-2"></i>Utilisateurs
                </a>
                
                <a href="{{ route('hr.attendance.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('hr.attendance*') ? 'active' : '' }}">
                    <i class="fas fa-clock me-2"></i>Mon Pointage
                </a>
                
                <!-- 🆕 Menu pour les rapports d'évaluation -->
                <a href="{{ route('hr.evaluation-reports.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('hr.evaluation-reports*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check me-2"></i>Rapports d'Évaluation
                    @php
                        $pendingReviews = \App\Models\EvaluationReport::where('status', 'sent')->count();
                    @endphp
                    @if($pendingReviews > 0)
                        <span class="badge bg-warning rounded-pill float-end notification-badge">{{ $pendingReviews }}</span>
                    @endif
                </a>

                <!-- 🆕 Menu pour la gestion de paie -->
                <a href="{{ route('hr.payroll.dashboard') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('hr.payroll*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave me-2"></i>Gestion Paie
                    @php
                        $pendingPayrolls = \App\Models\PayrollRecord::where('status', 'calculated')->count();
                        $employeesWithoutSalary = \App\Models\User::where('role', 'employee')
                                                                 ->whereDoesntHave('salaries', function($q) {
                                                                     $q->current();
                                                                 })->count();
                        $totalPendingPayroll = $pendingPayrolls + $employeesWithoutSalary;
                    @endphp
                    @if($totalPendingPayroll > 0)
                        <span class="badge bg-warning rounded-pill float-end notification-badge">{{ $totalPendingPayroll }}</span>
                    @endif
                </a>
                
                <div class="list-group-item bg-transparent text-white-50 border-0">
                    <small><i class="fas fa-cog me-2"></i>Administration</small>
                </div>
                
                <a href="#" class="list-group-item list-group-item-action bg-transparent text-white">
                    <i class="fas fa-chart-bar me-2"></i>Rapports Globaux
                </a>
                
                <a href="#" class="list-group-item list-group-item-action bg-transparent text-white">
                    <i class="fas fa-cogs me-2"></i>Paramètres
                </a>
            </div>
        </div>
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-dark" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="ms-auto">
                        <!-- Notifications rapides -->
                        <div class="dropdown d-inline-block me-3">
                            <button class="btn btn-outline-danger dropdown-toggle position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                @php
                                    $pendingReviews = \App\Models\EvaluationReport::where('status', 'sent')->count();
                                    $urgentReviews = \App\Models\EvaluationReport::where('status', 'sent')
                                        ->where('sent_at', '<=', now()->subDays(7))
                                        ->count();
                                    $pendingPayrolls = \App\Models\PayrollRecord::where('status', 'calculated')->count();
                                    $employeesWithoutSalary = \App\Models\User::where('role', 'employee')
                                                                             ->whereDoesntHave('salaries', function($q) {
                                                                                 $q->current();
                                                                             })->count();
                                    $totalNotifications = $pendingReviews + $pendingPayrolls + ($employeesWithoutSalary > 0 ? 1 : 0);
                                @endphp
                                @if($totalNotifications > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                                        {{ $totalNotifications }}
                                    </span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                                @if($pendingReviews > 0)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('hr.evaluation-reports.index') }}">
                                            <i class="fas fa-clipboard-check me-2 text-warning"></i>
                                            {{ $pendingReviews }} rapport(s) d'évaluation en attente
                                        </a>
                                    </li>
                                    @if($urgentReviews > 0)
                                        <li>
                                            <a class="dropdown-item text-danger" href="{{ route('hr.evaluation-reports.index', ['status' => 'sent']) }}">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $urgentReviews }} rapport(s) urgent(s) (>7 jours)
                                            </a>
                                        </li>
                                    @endif
                                @endif

                                @if($pendingPayrolls > 0)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('hr.payroll.index', ['status' => 'calculated']) }}">
                                            <i class="fas fa-money-bill-wave me-2 text-primary"></i>
                                            {{ $pendingPayrolls }} bulletin(s) de paie à approuver
                                        </a>
                                    </li>
                                @endif

                                @if($employeesWithoutSalary > 0)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('hr.payroll.salaries.index') }}">
                                            <i class="fas fa-exclamation-circle me-2 text-info"></i>
                                            {{ $employeesWithoutSalary }} employé(s) sans salaire défini
                                        </a>
                                    </li>
                                @endif

                                @if($totalNotifications > 0)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('hr.evaluation-reports.dashboard') }}">
                                            <i class="fas fa-chart-line me-2"></i>
                                            Tableau de bord des rapports
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('hr.payroll.dashboard') }}">
                                            <i class="fas fa-chart-bar me-2"></i>
                                            Tableau de bord de la paie
                                        </a>
                                    </li>
                                @else
                                    <li><span class="dropdown-item text-muted">Aucune notification</span></li>
                                @endif
                            </ul>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-tie me-2"></i>{{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i>Profil</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Paramètres</a></li>
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
    
    <script>
        // Auto-refresh des notifications toutes les 2 minutes
        setInterval(function() {
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newNotifications = doc.querySelector('#notificationDropdown');
                    if (newNotifications) {
                        document.querySelector('#notificationDropdown').outerHTML = newNotifications.outerHTML;
                    }
                })
                .catch(() => {});
        }, 120000); // Toutes les 2 minutes
    </script>
</body>
</html>