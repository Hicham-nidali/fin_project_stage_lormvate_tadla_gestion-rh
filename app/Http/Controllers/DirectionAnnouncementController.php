<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DirectionAnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with(['creator', 'reads'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Statistiques
        $totalAnnouncements = Announcement::count();
        $publishedAnnouncements = Announcement::where('status', 'published')->count();
        $draftAnnouncements = Announcement::where('status', 'draft')->count();
        $urgentAnnouncements = Announcement::where('priority', 'urgent')
                                         ->where('status', 'published')
                                         ->count();

        return view('direction.announcements.index', compact(
            'announcements', 
            'totalAnnouncements', 
            'publishedAnnouncements', 
            'draftAnnouncements',
            'urgentAnnouncements'
        ));
    }

    public function create()
    {
        return view('direction.announcements.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'meeting_date' => 'nullable|date|after:now',
            'meeting_location' => 'nullable|max:255',
            'priority' => 'required|in:normal,high,urgent',
            'status' => 'required|in:draft,published'
        ]);

        try {
            $announcement = Announcement::create([
                'title' => $request->title,
                'content' => $request->content,
                'meeting_date' => $request->meeting_date,
                'meeting_location' => $request->meeting_location,
                'priority' => $request->priority,
                'status' => $request->status,
                'created_by' => Auth::id()
            ]);

            $message = $request->status === 'published' 
                ? 'Annonce publiée avec succès' 
                : 'Annonce sauvegardée en brouillon';

            return redirect()->route('direction.announcements.index')
                           ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la création de l\'annonce')
                        ->withInput();
        }
    }

    public function show($id)
    {
        $announcement = Announcement::with(['creator', 'reads.user'])
                                  ->findOrFail($id);

        // Statistiques de lecture
        $totalUsers = User::whereIn('role', ['employee', 'department_head', 'hr_admin'])->count();
        $readUsers = $announcement->reads()->with('user')->get();
        $unreadUsers = User::whereIn('role', ['employee', 'department_head', 'hr_admin'])
                          ->whereNotIn('id', $readUsers->pluck('user_id'))
                          ->get();

        return view('direction.announcements.show', compact(
            'announcement', 
            'totalUsers', 
            'readUsers', 
            'unreadUsers'
        ));
    }

    public function edit($id)
    {
        $announcement = Announcement::findOrFail($id);
        
        if (!$announcement->canBeEdited()) {
            return redirect()->route('direction.announcements.index')
                           ->with('error', 'Cette annonce ne peut plus être modifiée');
        }

        return view('direction.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);
        
        if (!$announcement->canBeEdited()) {
            return redirect()->route('direction.announcements.index')
                           ->with('error', 'Cette annonce ne peut plus être modifiée');
        }

        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'meeting_date' => 'nullable|date|after:now',
            'meeting_location' => 'nullable|max:255',
            'priority' => 'required|in:normal,high,urgent',
            'status' => 'required|in:draft,published'
        ]);

        try {
            $announcement->update([
                'title' => $request->title,
                'content' => $request->content,
                'meeting_date' => $request->meeting_date,
                'meeting_location' => $request->meeting_location,
                'priority' => $request->priority,
                'status' => $request->status
            ]);

            $message = $request->status === 'published' 
                ? 'Annonce mise à jour et publiée avec succès' 
                : 'Annonce mise à jour et sauvegardée en brouillon';

            return redirect()->route('direction.announcements.index')
                           ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la mise à jour de l\'annonce')
                        ->withInput();
        }
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);

        try {
            $announcement->delete();
            return redirect()->route('direction.announcements.index')
                           ->with('success', 'Annonce supprimée avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression de l\'annonce');
        }
    }

    public function publish($id)
    {
        $announcement = Announcement::findOrFail($id);
        
        if (!$announcement->canBePublished()) {
            return back()->with('error', 'Cette annonce ne peut pas être publiée');
        }

        try {
            $announcement->update(['status' => 'published']);
            return back()->with('success', 'Annonce publiée avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la publication de l\'annonce');
        }
    }

    public function archive($id)
    {
        $announcement = Announcement::findOrFail($id);
        
        if (!$announcement->canBeArchived()) {
            return back()->with('error', 'Cette annonce ne peut pas être archivée');
        }

        try {
            $announcement->update(['status' => 'archived']);
            return back()->with('success', 'Annonce archivée avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'archivage de l\'annonce');
        }
    }

    public function dashboard()
    {
        // Statistiques générales
        $totalAnnouncements = Announcement::count();
        $publishedCount = Announcement::where('status', 'published')->count();
        $draftCount = Announcement::where('status', 'draft')->count();
        $urgentCount = Announcement::where('priority', 'urgent')
                                 ->where('status', 'published')
                                 ->count();

        // Annonces récentes
        $recentAnnouncements = Announcement::with('creator')
                                         ->orderBy('created_at', 'desc')
                                         ->take(5)
                                         ->get();

        // Réunions à venir
        $upcomingMeetings = Announcement::published()
                                      ->whereNotNull('meeting_date')
                                      ->where('meeting_date', '>=', now())
                                      ->orderBy('meeting_date')
                                      ->take(5)
                                      ->get();

        // Statistiques de lecture
        $totalUsers = User::whereIn('role', ['employee', 'department_head', 'hr_admin'])->count();
        
        $readStats = DB::table('announcements')
            ->leftJoin('announcement_reads', 'announcements.id', '=', 'announcement_reads.announcement_id')
            ->where('announcements.status', 'published')
            ->select('announcements.id', 'announcements.title', 
                    DB::raw('COUNT(announcement_reads.id) as read_count'))
            ->groupBy('announcements.id', 'announcements.title')
            ->orderBy('read_count', 'desc')
            ->take(5)
            ->get();

        return view('direction.announcements.dashboard', compact(
            'totalAnnouncements',
            'publishedCount',
            'draftCount', 
            'urgentCount',
            'recentAnnouncements',
            'upcomingMeetings',
            'totalUsers',
            'readStats'
        ));
    }

    public function readStats($id)
    {
        $announcement = Announcement::with(['reads.user.department'])->findOrFail($id);
        
        $readUsers = $announcement->reads()
                                 ->with('user.department')
                                 ->orderBy('read_at', 'desc')
                                 ->get();

        $unreadUsers = User::whereIn('role', ['employee', 'department_head', 'hr_admin'])
                          ->whereNotIn('id', $readUsers->pluck('user_id'))
                          ->with('department')
                          ->get();

        // Statistiques par département
        $departmentStats = DB::table('users')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoin('announcement_reads', function($join) use ($id) {
                $join->on('users.id', '=', 'announcement_reads.user_id')
                     ->where('announcement_reads.announcement_id', '=', $id);
            })
            ->whereIn('users.role', ['employee', 'department_head', 'hr_admin'])
            ->select('departments.name as department_name',
                    DB::raw('COUNT(users.id) as total_users'),
                    DB::raw('COUNT(announcement_reads.id) as read_count'))
            ->groupBy('departments.id', 'departments.name')
            ->get();

        return view('direction.announcements.read-stats', compact(
            'announcement',
            'readUsers',
            'unreadUsers',
            'departmentStats'
        ));
    }
}