<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Direction - Gestion Globale</title>
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
                <small class="text-white-50">Gestion Globale</small>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('direction.dashboard') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('direction.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                </a>
                
                <a href="{{ route('direction.users.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('direction.users*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog me-2"></i>Gestion Utilisateurs
                    @php
                        $unassignedUsers = \App\Models\User::whereNull('department_id')->count();
                    @endphp
                    @if($unassignedUsers > 0)
                        <span class="badge bg-warning rounded-pill float-end notification-badge">{{ $unassignedUsers }}</span>
                    @endif
                </a>
                
                <!-- NOUVEAU MENU ANNONCES -->
                <a href="{{ route('direction.announcements.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('direction.announcements*') ? 'active' : '' }}">
                    <i class="fas fa-bullhorn me-2"></i>Annonces & Réunions
                    @php
                        $draftAnnouncements = \App\Models\Announcement::where('status', 'draft')->count();
                        $todayMeetings = \App\Models\Announcement::published()
                                                                ->whereDate('meeting_date', today())
                                                                ->count();
                        $totalAlerts = $draftAnnouncements + $todayMeetings;
                    @endphp
                    @if($totalAlerts > 0)
                        <span class="badge bg-info rounded-pill float-end notification-badge">{{ $totalAlerts }}</span>
                    @endif
                </a>
                
                <a href="{{ route('direction.attendance') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('direction.attendance*') ? 'active' : '' }}">
                    <i class="fas fa-users-clock me-2"></i>Présences Globales
                </a>
                
                <a href="{{ route('direction.objectives.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('direction.objectives*') ? 'active' : '' }}">
                    <i class="fas fa-bullseye me-2"></i>Objectifs Départementaux
                    @php
                        $overdueObjectives = \App\Models\Objective::overdue()->count();
                        $criticalObjectives = \App\Models\Objective::critical()->active()->count();
                        $totalObjectiveAlerts = $overdueObjectives + $criticalObjectives;
                    @endphp
                    @if($totalObjectiveAlerts > 0)
                        <span class="badge bg-warning rounded-pill float-end notification-badge">{{ $totalObjectiveAlerts }}</span>
                    @endif
                </a>
                
                <!-- MENU RAPPORTS -->
                <div class="dropdown">
                    <a href="#" class="list-group-item list-group-item-action bg-transparent text-white dropdown-toggle {{ request()->routeIs('direction.reports*') ? 'active' : '' }}" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-line me-2"></i>Rapports Globaux
                        @php
                            $pendingReports = \App\Models\EvaluationReport::where('status', 'sent')->count();
                            $reportsThisMonth = \App\Models\Report::whereMonth('created_at', now()->month)->count() + 
                                              \App\Models\EvaluationReport::whereMonth('created_at', now()->month)->count();
                        @endphp
                        @if($pendingReports > 0)
                            <span class="badge bg-info rounded-pill float-end notification-badge">{{ $pendingReports }}</span>
                        @endif
                    </a>
                    <ul class="dropdown-menu bg-dark border-0 mt-1 ms-3">
                        <li>
                            <a class="dropdown-item text-white bg-transparent {{ request()->routeIs('direction.reports.dashboard') ? 'active' : '' }}" 
                               href="{{ route('direction.reports.dashboard') }}">
                                <i class="fas fa-chart-bar me-2"></i>Tableau de Bord
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item text-white bg-transparent {{ request()->routeIs('direction.reports.index') ? 'active' : '' }}" 
                               href="{{ route('direction.reports.index') }}">
                                <i class="fas fa-list me-2"></i>Tous les Rapports
                                <span class="badge bg-primary rounded-pill float-end">{{ $reportsThisMonth }}</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item text-white bg-transparent" 
                               href="{{ route('direction.reports.index', ['type' => 'evaluation']) }}">
                                <i class="fas fa-clipboard-check me-2"></i>Rapports d'Évaluation
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item text-white bg-transparent" 
                               href="{{ route('direction.reports.index', ['type' => 'monthly']) }}">
                                <i class="fas fa-calendar-alt me-2"></i>Rapports Mensuels
                            </a>
                        </li>
                        <li><hr class="dropdown-divider bg-secondary"></li>
                        <li>
                            <a class="dropdown-item text-white bg-transparent" 
                               href="{{ route('direction.reports.export', ['type' => 'all']) }}">
                                <i class="fas fa-download me-2"></i>Exporter Tout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-dark me-3" id="menu-toggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        
                        <!-- Logo ORMVAT -->
                        <div class="navbar-brand d-flex align-items-center">
                            <img src="{{ asset('images/logo-ormvat.png') }}" alt="ORMVAT" class="navbar-logo me-2">
                            <span class="navbar-brand-text">ORMVAT</span>
                        </div>
                    </div>
                    
                    <div class="ms-auto">
                        <!-- Notifications annonces -->
                        @php
                            $draftCount = \App\Models\Announcement::where('status', 'draft')->count();
                            $todayMeetingsCount = \App\Models\Announcement::published()
                                                                        ->whereDate('meeting_date', today())
                                                                        ->count();
                            $urgentUnread = \App\Models\Announcement::published()
                                                                   ->where('priority', 'urgent')
                                                                   ->where('created_at', '>=', now()->subDays(1))
                                                                   ->count();
                            $totalAnnouncementNotifications = $draftCount + $todayMeetingsCount + $urgentUnread;
                        @endphp
                        
                        @if($totalAnnouncementNotifications > 0)
                        <div class="dropdown d-inline-block me-2">
                            <button class="btn btn-outline-primary position-relative" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bullhorn"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary announcement-notification-badge">
                                    {{ $totalAnnouncementNotifications }}
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Annonces & Réunions</h6></li>
                                
                                @if($draftCount > 0)
                                <li>
                                    <a class="dropdown-item" href="{{ route('direction.announcements.index', ['status' => 'draft']) }}">
                                        <i class="fas fa-edit me-2 text-warning"></i>
                                        {{ $draftCount }} brouillon(s) d'annonce
                                    </a>
                                </li>
                                @endif
                                
                                @if($todayMeetingsCount > 0)
                                <li>
                                    <a class="dropdown-item" href="{{ route('direction.announcements.index') }}">
                                        <i class="fas fa-calendar-day me-2 text-danger"></i>
                                        {{ $todayMeetingsCount }} réunion(s) aujourd'hui
                                    </a>
                                </li>
                                @endif
                                
                                @if($urgentUnread > 0)
                                <li>
                                    <a class="dropdown-item" href="{{ route('direction.announcements.index', ['priority' => 'urgent']) }}">
                                        <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                        {{ $urgentUnread }} annonce(s) urgente(s) récente(s)
                                    </a>
                                </li>
                                @endif
                                
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('direction.announcements.dashboard') }}">
                                        <i class="fas fa-chart-bar me-2"></i>
                                        Tableau de bord des annonces
                                    </a>
                                </li>
                            </ul>
                        </div>
                        @endif
                        
                        <!-- Notifications rapports -->
                        @if($pendingReports > 0)
                        <div class="dropdown d-inline-block me-2">
                            <button class="btn btn-outline-warning position-relative" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                    {{ $pendingReports }}
                                </span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><h6 class="dropdown-header">Rapports en Attente</h6></li>
                                <li><a class="dropdown-item" href="{{ route('direction.reports.index', ['status' => 'sent']) }}">
                                    {{ $pendingReports }} rapport(s) d'évaluation à examiner
                                </a></li>
                            </ul>
                        </div>
                        @endif
                        
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