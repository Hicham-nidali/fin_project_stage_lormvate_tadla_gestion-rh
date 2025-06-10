<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

class DepartmentHeadAnnouncementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Récupérer toutes les annonces publiées
        $announcements = Announcement::published()
            ->with(['creator', 'reads' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Statistiques
        $totalAnnouncements = Announcement::published()->count();
        $unreadCount = Announcement::getUnreadCountForUser($user);
        $urgentUnreadCount = Announcement::getUrgentUnreadCountForUser($user);
        $todayMeetings = Announcement::getTodayMeetings();
        $upcomingMeetings = Announcement::getUpcomingMeetings(7);

        return view('department-head.announcements.index', compact(
            'announcements',
            'totalAnnouncements',
            'unreadCount',
            'urgentUnreadCount',
            'todayMeetings',
            'upcomingMeetings'
        ));
    }

    public function show($id)
    {
        $user = Auth::user();
        $announcement = Announcement::published()
                                  ->with(['creator'])
                                  ->findOrFail($id);

        // Marquer comme lu
        $announcement->markAsReadBy($user);

        return view('department-head.announcements.show', compact('announcement'));
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $announcement = Announcement::published()->findOrFail($id);
        
        $announcement->markAsReadBy($user);

        return response()->json([
            'success' => true,
            'message' => 'Annonce marquée comme lue'
        ]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        
        $unreadAnnouncements = Announcement::published()
            ->whereDoesntHave('reads', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        foreach ($unreadAnnouncements as $announcement) {
            $announcement->markAsReadBy($user);
        }

        return response()->json([
            'success' => true,
            'message' => 'Toutes les annonces marquées comme lues',
            'count' => $unreadAnnouncements->count()
        ]);
    }

    public function getUnreadCount()
    {
        $user = Auth::user();
        $unreadCount = Announcement::getUnreadCountForUser($user);
        $urgentCount = Announcement::getUrgentUnreadCountForUser($user);

        return response()->json([
            'unread_count' => $unreadCount,
            'urgent_count' => $urgentCount
        ]);
    }

    // Méthode spécifique aux chefs de département pour voir les stats de leur équipe
    public function teamReadingStats()
    {
        $user = Auth::user();
        
        if (!$user->department_id) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes assigné à aucun département');
        }

        // Récupérer les annonces publiées
        $announcements = Announcement::published()
                                   ->orderBy('created_at', 'desc')
                                   ->take(10)
                                   ->get();

        // Récupérer les membres du département
        $teamMembers = \App\Models\User::where('department_id', $user->department_id)
                                      ->where('role', 'employee')
                                      ->with('department')
                                      ->get();

        // Calculer les statistiques de lecture pour chaque annonce
        $readingStats = [];
        foreach ($announcements as $announcement) {
            $readCount = $announcement->reads()
                                    ->whereIn('user_id', $teamMembers->pluck('id'))
                                    ->count();
            
            $readingStats[] = [
                'announcement' => $announcement,
                'team_read_count' => $readCount,
                'team_total' => $teamMembers->count(),
                'team_percentage' => $teamMembers->count() > 0 ? round(($readCount / $teamMembers->count()) * 100, 1) : 0
            ];
        }

        return view('department-head.announcements.team-stats', compact(
            'announcements',
            'teamMembers',
            'readingStats'
        ));
    }
}