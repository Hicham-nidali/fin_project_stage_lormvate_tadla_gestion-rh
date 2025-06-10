<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

class EmployeeAnnouncementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Récupérer toutes les annonces publiées
        $announcements = Announcement::published()
            ->with(['creator', 'reads' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->orderBy('priority', 'desc') // Urgent en premier
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Statistiques
        $totalAnnouncements = Announcement::published()->count();
        $unreadCount = Announcement::getUnreadCountForUser($user);
        $urgentUnreadCount = Announcement::getUrgentUnreadCountForUser($user);
        $todayMeetings = Announcement::getTodayMeetings();

        return view('employee.announcements.index', compact(
            'announcements',
            'totalAnnouncements',
            'unreadCount',
            'urgentUnreadCount',
            'todayMeetings'
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

        return view('employee.announcements.show', compact('announcement'));
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
}