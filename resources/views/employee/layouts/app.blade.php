<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Système de Gestion - Employé</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-success" id="sidebar-wrapper">
            <div class="sidebar-heading text-white text-center py-4">
                <h4>Espace Employé</h4>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('employee.dashboard') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord
                </a>
                
                <!-- NOUVEAU MENU ANNONCES -->
                <a href="{{ route('employee.announcements.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('employee.announcements*') ? 'active' : '' }}">
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
                
                <a href="{{ route('employee.attendance.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('employee.attendance*') ? 'active' : '' }}">
                    <i class="fas fa-clock me-2"></i>Pointage
                </a>
                <a href="{{ route('employee.tasks.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('employee.tasks*') ? 'active' : '' }}">
                    <i class="fas fa-tasks me-2"></i>Mes Tâches
                </a>
                <a href="{{ route('employee.requests.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('employee.requests*') ? 'active' : '' }}">
                    <i class="fas fa-paper-plane me-2"></i>Mes Demandes
                </a>
                <a href="{{ route('employee.messages.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('employee.messages*') ? 'active' : '' }}">
                    <i class="fas fa-envelope me-2"></i>Messages
                </a>
                <a href="{{ route('employee.attendance.history') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('employee.attendance.history') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check me-2"></i>Mon Historique
                </a>
                <a href="{{ route('employee.profile.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('employee.profile*') ? 'active' : '' }}">
                    <i class="fas fa-user me-2"></i>Mon Profil
                </a>
                <!-- Menu pour les bulletins de paie -->
                <a href="{{ route('employee.payroll.index') }}" class="list-group-item list-group-item-action bg-transparent text-white {{ request()->routeIs('employee.payroll*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Mes Bulletins de Paie
                    @php
                        $currentPeriod = \App\Models\PayrollRecord::generatePeriod();
                        $currentPayroll = \App\Models\PayrollRecord::where('user_id', Auth::id())
                                                                  ->where('period', $currentPeriod)
                                                                  ->where('status', '!=', 'draft')
                                                                  ->exists();
                    @endphp
                    @if($currentPayroll)
                        <span class="badge bg-info rounded-pill float-end notification-badge">
                            <i class="fas fa-check"></i>
                        </span>
                    @endif
                </a>
            </div>
        </div>
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-success me-3" id="menu-toggle">
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
                                    <a class="dropdown-item text-danger" href="{{ route('employee.announcements.index', ['priority' => 'urgent', 'read_status' => 'unread']) }}">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        {{ $urgentUnread }} annonce(s) urgente(s) non lue(s)
                                    </a>
                                </li>
                                @endif
                                
                                @if($unreadAnnouncements > 0)
                                <li>
                                    <a class="dropdown-item" href="{{ route('employee.announcements.index', ['read_status' => 'unread']) }}">
                                        <i class="fas fa-envelope me-2 text-warning"></i>
                                        {{ $unreadAnnouncements }} annonce(s) non lue(s)
                                    </a>
                                </li>
                                @endif
                                
                                @if($todayMeetings->count() > 0)
                                <li>
                                    <a class="dropdown-item" href="{{ route('employee.announcements.index') }}">
                                        <i class="fas fa-calendar-day me-2 text-danger"></i>
                                        {{ $todayMeetings->count() }} réunion(s) aujourd'hui
                                    </a>
                                </li>
                                @endif
                                
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('employee.announcements.index') }}">
                                        <i class="fas fa-list me-2"></i>
                                        Voir toutes les annonces
                                    </a>
                                </li>
                            </ul>
                        </div>
                        @endif

                        <!-- Notifications rapides pour l'employé -->
                        <div class="dropdown d-inline-block me-3">
                            <button class="btn btn-outline-success dropdown-toggle position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                @php
                                    $pendingTasks = Auth::user()->tasks()->whereIn('status', ['pending', 'in_progress'])->count();
                                    $pendingRequests = \App\Models\Request::where('user_id', Auth::id())->where('status', 'pending')->count();
                                    $newPayroll = \App\Models\PayrollRecord::where('user_id', Auth::id())
                                                                          ->where('period', $currentPeriod)
                                                                          ->where('status', '!=', 'draft')
                                                                          ->where('created_at', '>=', now()->subDays(7))
                                                                          ->exists();
                                    $totalNotifications = $pendingTasks + $pendingRequests + ($newPayroll ? 1 : 0);
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
                                        <a class="dropdown-item" href="{{ route('employee.tasks.index') }}">
                                            <i class="fas fa-tasks me-2 text-primary"></i>
                                            {{ $pendingTasks }} tâche(s) en cours
                                        </a>
                                    </li>
                                @endif

                                @if($pendingRequests > 0)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('employee.requests.index') }}">
                                            <i class="fas fa-paper-plane me-2 text-warning"></i>
                                            {{ $pendingRequests }} demande(s) en attente
                                        </a>
                                    </li>
                                @endif

                                @if($newPayroll)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('employee.payroll.index') }}">
                                            <i class="fas fa-file-invoice-dollar me-2 text-success"></i>
                                            Nouveau bulletin de paie disponible
                                        </a>
                                    </li>
                                @endif

                                @if($totalNotifications > 0)
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('employee.dashboard') }}">
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
                                <i class="fas fa-user me-2"></i>{{ Auth::user()->name ?? 'Employé' }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ route('employee.profile.index') }}"><i class="fas fa-user-cog me-2"></i>Profil</a></li>
                                <li><a class="dropdown-item" href="{{ route('employee.announcements.index') }}"><i class="fas fa-bullhorn me-2"></i>Annonces Direction</a></li>
                                <li><a class="dropdown-item" href="{{ route('employee.payroll.index') }}"><i class="fas fa-file-invoice-dollar me-2"></i>Mes Bulletins de Paie</a></li>
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
            fetch('/employee/announcements/unread-count')
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