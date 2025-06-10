<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chef de Département - Système de Gestion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-primary" id="sidebar-wrapper">
            <div class="sidebar-heading text-white text-center py-4">
                <h4>Chef de Département</h4>
                <small class="text-white-50">{{ Auth::user()->department->name ?? 'Aucun département' }}</small>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                </a>
                
                <!-- NOUVEAU MENU ANNONCES -->
                <a href="{{ route('announcements.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('announcements*') ? 'active' : '' }}">
                    <i class="fas fa-bullhorn me-2"></i>Annonces Direction
                    @php
                        $unreadAnnouncements = \App\Models\Announcement::getUnreadCountForUser(Auth::user());
                        $urgentUnread = \App\Models\Announcement::getUrgentUnreadCountForUser(Auth::user());
                        $todayMeetings = \App\Models\Announcement::getTodayMeetings()->count();
                        $totalAnnouncementAlerts = $unreadAnnouncements + $todayMeetings;
                    @endphp
                    @if($totalAnnouncementAlerts > 0)
                        <span class="badge bg-warning rounded-pill float-end notification-badge announcement-notification-badge">{{ $totalAnnouncementAlerts }}</span>
                    @endif
                </a>
                
                <a href="{{ route('team.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('team*') ? 'active' : '' }}">
                    <i class="fas fa-users me-2"></i>Mon Équipe
                    @php
                        $teamSize = Auth::user()->department ? Auth::user()->department->users->where('role', 'employee')->count() : 0;
                    @endphp
                    @if($teamSize > 0)
                        <span class="badge bg-info rounded-pill float-end">{{ $teamSize }}</span>
                    @endif
                </a>
                
                <a href="{{ route('tasks.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('tasks*') ? 'active' : '' }}">
                    <i class="fas fa-tasks me-2"></i>Gestion des Tâches
                    @php
                        $pendingTasks = \App\Models\Task::where('assigned_to', Auth::id())->where('status', 'pending')->count();
                    @endphp
                    @if($pendingTasks > 0)
                        <span class="badge bg-warning rounded-pill float-end notification-badge">{{ $pendingTasks }}</span>
                    @endif
                </a>
                
                <a href="{{ route('attendance.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('attendance*') ? 'active' : '' }}">
                    <i class="fas fa-clock me-2"></i>Présences Équipe
                </a>
                
                <a href="{{ route('department-head.attendance.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('department-head.attendance*') ? 'active' : '' }}">
                    <i class="fas fa-user-clock me-2"></i>Mon Pointage
                </a>
                
                <a href="{{ route('evaluations.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('evaluations*') ? 'active' : '' }}">
                    <i class="fas fa-star me-2"></i>Évaluations
                </a>
                
                <a href="{{ route('evaluation-reports.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('evaluation-reports*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check me-2"></i>Rapports d'Évaluation
                    @php
                        $draftReports = \App\Models\EvaluationReport::where('created_by', Auth::id())->where('status', 'draft')->count();
                    @endphp
                    @if($draftReports > 0)
                        <span class="badge bg-secondary rounded-pill float-end notification-badge">{{ $draftReports }}</span>
                    @endif
                </a>
                
                <a href="{{ route('requests.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('requests*') ? 'active' : '' }}">
                    <i class="fas fa-inbox me-2"></i>Demandes Employés
                    @php
                        $pendingRequests = \App\Models\Request::whereHas('user', function($q) {
                            $q->where('department_id', Auth::user()->department_id);
                        })->where('status', 'pending')->count();
                    @endphp
                    @if($pendingRequests > 0)
                        <span class="badge bg-warning rounded-pill float-end notification-badge">{{ $pendingRequests }}</span>
                    @endif
                </a>
                
                <a href="{{ route('reports.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('reports*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line me-2"></i>Rapports
                </a>
                
                <a href="{{ route('objectives.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('objectives*') ? 'active' : '' }}">
                    <i class="fas fa-bullseye me-2"></i>Objectifs Département
                    @php
                        $activeObjectives = Auth::user()->getActiveObjectivesCount();
                        $overdueObjectives = Auth::user()->getOverdueObjectivesCount();
                    @endphp
                    @if($overdueObjectives > 0)
                        <span class="badge bg-danger rounded-pill float-end notification-badge">{{ $overdueObjectives }}</span>
                    @elseif($activeObjectives > 0)
                        <span class="badge bg-info rounded-pill float-end">{{ $activeObjectives }}</span>
                    @endif
                </a>
            </div>
        </div>
        
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-primary me-3" id="menu-toggle">
                            <i class="fas fa-bars"></i>
                        </button>
                        
                        <!-- Logo ORMVAT -->
                        <div class="navbar-brand d-flex align-items-center">
                            <img src="{{ asset('images/logo-ormvat.png') }}" alt="ORMVAT" class="navbar-logo me-2">
                            <span class="navbar-brand-text">ORMVAT</span>
                        </div>
                    </div>
                    
                    <div class="ms-auto">
                        <!-- Notifications annonces Direction -->
                        @php
                            $unreadAnnouncements = \App\Models\Announcement::getUnreadCountForUser(Auth::user());
                            $urgentUnread = \App\Models\Announcement::getUrgentUnreadCountForUser(Auth::user());
                            $todayMeetings = \App\Models\Announcement::getTodayMeetings();
                        @endphp
                        
                        @if($unreadAnnouncements > 0 || $todayMeetings->count() > 0 || $urgentUnread > 0)
                        <div class="dropdown d-inline-block me-2">
                            <button class="btn btn-outline-primary position-relative" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bullhorn"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary announcement-notification-badge">
                                    {{ $unreadAnnouncements + $todayMeetings->count() }}
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Annonces Direction</h6></li>
                                
                                @if($urgentUnread > 0)
                                <li>
                                    <a class="dropdown-item text-danger" href="{{ route('announcements.index', ['priority' => 'urgent', 'read_status' => 'unread']) }}">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        {{ $urgentUnread }} annonce(s) urgente(s) non lue(s)
                                    </a>
                                </li>
                                @endif
                                
                                @if($unreadAnnouncements > 0)
                                <li>
                                    <a class="dropdown-item" href="{{ route('announcements.index', ['read_status' => 'unread']) }}">
                                        <i class="fas fa-envelope me-2 text-warning"></i>
                                        {{ $unreadAnnouncements }} annonce(s) non lue(s)
                                    </a>
                                </li>
                                @endif
                                
                                @if($todayMeetings->count() > 0)
                                <li>
                                    <a class="dropdown-item" href="{{ route('announcements.index') }}">
                                        <i class="fas fa-calendar-day me-2 text-danger"></i>
                                        {{ $todayMeetings->count() }} réunion(s) aujourd'hui
                                    </a>
                                </li>
                                @endif
                                
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('announcements.index') }}">
                                        <i class="fas fa-list me-2"></i>
                                        Voir toutes les annonces
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('announcements.team-reading-stats') }}">
                                        <i class="fas fa-chart-line me-2"></i>
                                        Stats de lecture équipe
                                    </a>
                                </li>
                            </ul>
                        </div>
                        @endif

                        <!-- Notifications rapides -->
                        <div class="dropdown d-inline-block me-3">
                            <button class="btn btn-outline-primary dropdown-toggle position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                @php
                                    $pendingTasks = \App\Models\Task::where('assigned_to', Auth::id())->where('status', 'pending')->count();
                                    $pendingRequests = \App\Models\Request::whereHas('user', function($q) {
                                        $q->where('department_id', Auth::user()->department_id);
                                    })->where('status', 'pending')->count();
                                    $draftReports = \App\Models\EvaluationReport::where('created_by', Auth::id())->where('status', 'draft')->count();
                                    $overdueObjectives = Auth::user()->getOverdueObjectivesCount();
                                    $totalNotifications = $pendingTasks + $pendingRequests + $draftReports + $overdueObjectives;
                                @endphp
                                @if($totalNotifications > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                                        {{ $totalNotifications }}
                                    </span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                                @if($pendingTasks > 0)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('tasks.index') }}">
                                            <i class="fas fa-tasks me-2 text-warning"></i>
                                            {{ $pendingTasks }} tâche(s) en attente
                                        </a>
                                    </li>
                                @endif

                                @if($pendingRequests > 0)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('requests.index') }}">
                                            <i class="fas fa-inbox me-2 text-info"></i>
                                            {{ $pendingRequests }} demande(s) employé(s)
                                        </a>
                                    </li>
                                @endif

                                @if($draftReports > 0)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('evaluation-reports.index') }}">
                                            <i class="fas fa-clipboard-check me-2 text-secondary"></i>
                                            {{ $draftReports }} rapport(s) en brouillon
                                        </a>
                                    </li>
                                @endif

                                @if($overdueObjectives > 0)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('objectives.index') }}">
                                            <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                                            {{ $overdueObjectives }} objectif(s) en retard
                                        </a>
                                    </li>
                                @endif

                                @if($totalNotifications > 0)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('dashboard') }}">
                                            <i class="fas fa-tachometer-alt me-2"></i>
                                            Voir le tableau de bord
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
                                <li><span class="dropdown-item-text"><strong>Chef de Département</strong></span></li>
                                <li><span class="dropdown-item-text small text-muted">{{ Auth::user()->department->name ?? 'Aucun département' }}</span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('announcements.index') }}"><i class="fas fa-bullhorn me-2"></i>Annonces Direction</a></li>
                                <li><a class="dropdown-item" href="{{ route('team.index') }}"><i class="fas fa-users me-2"></i>Mon Équipe</a></li>
                                <li><a class="dropdown-item" href="{{ route('objectives.index') }}"><i class="fas fa-bullseye me-2"></i>Mes Objectifs</a></li>
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
        // Auto-refresh des notifications d'annonces toutes les 2 minutes
        setInterval(function() {
            fetch('/announcements/api/unread-count')
                .then(response => response.json())
                .then(data => {
                    // Mettre à jour les badges de notification
                    const badges = document.querySelectorAll('.announcement-notification-badge');
                    badges.forEach(badge => {
                        if (data.unread_count > 0) {
                            badge.textContent = data.unread_count;
                            badge.style.display = 'inline';
                        } else {
                            badge.style.display = 'none';
                        }
                    });
                })
                .catch(error => console.error('Erreur:', error));
        }, 120000); // 2 minutes
    </script>
</body>
</html>