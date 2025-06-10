<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class HRAnnouncementController extends Controller
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

        // Statistiques globales
        $totalAnnouncements = Announcement::published()->count();
        $unreadCount = Announcement::getUnreadCountForUser($user);
        $urgentUnreadCount = Announcement::getUrgentUnreadCountForUser($user);
        $todayMeetings = Announcement::getTodayMeetings();
        $upcomingMeetings = Announcement::getUpcomingMeetings(7);

        return view('hr.announcements.index', compact(
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

        return view('hr.announcements.show', compact('announcement'));
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

    // Statistiques globales pour l'admin RH
    public function globalStats()
    {
        // Récupérer toutes les annonces publiées
        $announcements = Announcement::published()
                                   ->orderBy('created_at', 'desc')
                                   ->get();

        // Statistiques globales
        $totalUsers = User::whereIn('role', ['employee', 'department_head', 'hr_admin'])->count();
        $departments = Department::with('users')->get();

        // Statistiques par annonce
        $announcementStats = $announcements->map(function($announcement) use ($totalUsers) {
            $readCount = $announcement->getReadCount();
            return [
                'announcement' => $announcement,
                'read_count' => $readCount,
                'unread_count' => $totalUsers - $readCount,
                'read_percentage' => $announcement->getReadPercentage()
            ];
        });

        // Statistiques par département
        $departmentStats = $departments->map(function($department) use ($announcements) {
            $departmentUsers = $department->users()
                                         ->whereIn('role', ['employee', 'department_head', 'hr_admin'])
                                         ->get();
            
            $totalReads = 0;
            $possibleReads = $departmentUsers->count() * $announcements->count();
            
            foreach ($announcements as $announcement) {
                $totalReads += $announcement->reads()
                                          ->whereIn('user_id', $departmentUsers->pluck('id'))
                                          ->count();
            }
            
            return [
                'department' => $department,
                'total_users' => $departmentUsers->count(),
                'total_reads' => $totalReads,
                'possible_reads' => $possibleReads,
                'reading_rate' => $possibleReads > 0 ? round(($totalReads / $possibleReads) * 100, 1) : 0
            ];
        });

        // Utilisateurs avec le moins de lectures
        $userReadingStats = User::whereIn('role', ['employee', 'department_head', 'hr_admin'])
                               ->with('department')
                               ->get()
                               ->map(function($user) use ($announcements) {
                                   $readCount = 0;
                                   foreach ($announcements as $announcement) {
                                       if ($announcement->isReadBy($user)) {
                                           $readCount++;
                                       }
                                   }
                                   
                                   return [
                                       'user' => $user,
                                       'read_count' => $readCount,
                                       'total_announcements' => $announcements->count(),
                                       'reading_rate' => $announcements->count() > 0 ? round(($readCount / $announcements->count()) * 100, 1) : 0
                                   ];
                               })
                               ->sortBy('reading_rate');

        return view('hr.announcements.global-stats', compact(
            'announcements',
            'announcementStats',
            'departmentStats',
            'userReadingStats',
            'totalUsers'
        ));
    }

    // Export des statistiques
    public function exportStats()
    {
        $announcements = Announcement::published()->get();
        $users = User::whereIn('role', ['employee', 'department_head', 'hr_admin'])
                    ->with('department')
                    ->get();

        $csvData = [];
        $csvData[] = ['Nom', 'Email', 'Département', 'Rôle', 'Total Annonces', 'Annonces Lues', 'Taux de Lecture (%)'];

        foreach ($users as $user) {
            $readCount = 0;
            foreach ($announcements as $announcement) {
                if ($announcement->isReadBy($user)) {
                    $readCount++;
                }
            }
            
            $readingRate = $announcements->count() > 0 ? round(($readCount / $announcements->count()) * 100, 1) : 0;
            
            $csvData[] = [
                $user->name,
                $user->email,
                $user->department->name ?? 'Aucun',
                $user->role,
                $announcements->count(),
                $readCount,
                $readingRate
            ];
        }

        $filename = 'statistiques_annonces_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $handle = fopen('php://temp', 'w+');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
               ->header('Content-Type', 'text/csv')
               ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}