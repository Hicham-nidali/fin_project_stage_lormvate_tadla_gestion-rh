<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class DirectionController extends Controller
{
    public function dashboard()
    {
        // Statistiques globales simples
        $totalUsers = User::whereIn('role', ['employee', 'department_head', 'hr_admin'])->count();
        
        // Présences du jour
        $today = Carbon::today()->toDateString();
        $todayAttendances = Attendance::where('date', $today)->count();
        $presentToday = Attendance::where('date', $today)->where('status', 'present')->count();
        $absentToday = Attendance::where('date', $today)->where('status', 'absent')->count();
        $lateToday = Attendance::where('date', $today)->where('status', 'late')->count();
        $notRecorded = $totalUsers - $todayAttendances;

        return view('direction.dashboard', compact(
            'totalUsers',
            'presentToday',
            'absentToday',
            'lateToday',
            'notRecorded'
        ));
    }

    public function attendance(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        
        // Toutes les présences pour la date sélectionnée
        $attendances = Attendance::with(['user.department'])
            ->where('date', $date)
            ->get();
        
        // Utilisateurs sans présence enregistrée
        $usersWithoutAttendance = User::with('department')
            ->whereIn('role', ['employee', 'department_head', 'hr_admin'])
            ->whereNotIn('id', Attendance::where('date', $date)->pluck('user_id'))
            ->get();
        
        // Statistiques
        $totalUsers = User::whereIn('role', ['employee', 'department_head', 'hr_admin'])->count();
        $totalPresent = $attendances->where('status', 'present')->count();
        $totalAbsent = $attendances->where('status', 'absent')->count();
        $totalLate = $attendances->where('status', 'late')->count();
        $totalNotRecorded = $usersWithoutAttendance->count();

        return view('direction.attendance', compact(
            'attendances',
            'usersWithoutAttendance', 
            'date',
            'totalUsers',
            'totalPresent',
            'totalAbsent',
            'totalLate',
            'totalNotRecorded'
        ));
    }

    public function attendanceReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        // Rapport de présence global par période
        $attendanceData = Attendance::with(['user.department'])
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        // Statistiques globales pour la période
        $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $totalUsers = User::whereIn('role', ['employee', 'department_head', 'hr_admin'])->count();
        $totalPossibleAttendances = $totalUsers * $totalDays;
        $totalRecordedAttendances = $attendanceData->count();
        $globalAttendanceRate = $totalPossibleAttendances > 0 ? round(($totalRecordedAttendances / $totalPossibleAttendances) * 100, 1) : 0;
        
        return view('direction.attendance-report', compact(
            'attendanceData',
            'startDate',
            'endDate',
            'totalDays',
            'globalAttendanceRate',
            'totalUsers'
        ));
    }

    // ==================== GESTION UTILISATEURS ====================
    
    public function usersIndex()
    {
        $users = User::with('department')->get();
        $departments = Department::all();
        
        // Statistiques
        $totalUsers = $users->count();
        $totalEmployees = $users->where('role', 'employee')->count();
        $totalHeads = $users->where('role', 'department_head')->count();
        $totalHR = $users->where('role', 'hr_admin')->count();
        $totalDirection = $users->where('role', 'direction')->count();
        $unassignedUsers = $users->whereNull('department_id')->count();
        
        return view('direction.users.index', compact(
            'users', 
            'departments', 
            'totalUsers', 
            'totalEmployees', 
            'totalHeads', 
            'totalHR', 
            'totalDirection',
            'unassignedUsers'
        ));
    }

    public function usersCreate()
    {
        $departments = Department::all();
        return view('direction.users.create', compact('departments'));
    }

    public function usersStore(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:employee,department_head,hr_admin',
            'department_id' => 'nullable|exists:departments,id'
        ]);

        try {
            DB::beginTransaction();
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'department_id' => $request->department_id
            ]);
            
            // Si c'est un chef de département, l'assigner au département
            if ($request->role == 'department_head' && $request->department_id) {
                Department::where('id', $request->department_id)
                          ->update(['head_id' => $user->id]);
            }
            
            DB::commit();
            return redirect()->route('direction.users.index')
                           ->with('success', 'Utilisateur créé avec succès');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors de la création de l\'utilisateur')
                        ->withInput();
        }
    }

    public function usersEdit($id)
    {
        $user = User::findOrFail($id);
        $departments = Department::all();
        return view('direction.users.edit', compact('user', 'departments'));
    }

    public function usersUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Empêcher la modification des comptes direction
        if ($user->role === 'direction') {
            return back()->with('error', 'Impossible de modifier un compte Direction');
        }
        
        $request->validate([
            'name' => 'required|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:employee,department_head,hr_admin',
            'department_id' => 'nullable|exists:departments,id'
        ]);

        try {
            DB::beginTransaction();
            
            $oldRole = $user->role;
            $oldDepartmentId = $user->department_id;
            
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'department_id' => $request->department_id
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);
            
            // Gestion des changements de rôle et département
            if ($oldRole == 'department_head' && $request->role != 'department_head') {
                // Retirer de chef de département
                Department::where('head_id', $user->id)->update(['head_id' => null]);
            }
            
            if ($request->role == 'department_head' && $request->department_id) {
                // Retirer l'ancien chef s'il y en a un
                Department::where('id', $request->department_id)
                          ->where('head_id', '!=', $user->id)
                          ->update(['head_id' => null]);
                // Assigner le nouveau chef
                Department::where('id', $request->department_id)
                          ->update(['head_id' => $user->id]);
            }
            
            DB::commit();
            return redirect()->route('direction.users.index')
                           ->with('success', 'Utilisateur modifié avec succès');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors de la modification de l\'utilisateur')
                        ->withInput();
        }
    }

    public function usersDestroy($id)
    {
        $user = User::findOrFail($id);
        
        // Empêcher la suppression de son propre compte
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Impossible de supprimer votre propre compte');
        }
        
        // Empêcher la suppression des comptes direction
        if ($user->role === 'direction') {
            return back()->with('error', 'Impossible de supprimer un compte Direction');
        }
        
        try {
            DB::beginTransaction();
            
            // Si c'est un chef de département, retirer la référence
            if ($user->role === 'department_head') {
                Department::where('head_id', $user->id)->update(['head_id' => null]);
            }
            
            $user->delete();
            
            DB::commit();
            return redirect()->route('direction.users.index')
                           ->with('success', 'Utilisateur supprimé avec succès');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors de la suppression de l\'utilisateur');
        }
    }

    public function toggleUserStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Empêcher de désactiver son propre compte
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Impossible de modifier votre propre statut');
        }
        
        // Empêcher de modifier le statut des comptes direction
        if ($user->role === 'direction') {
            return back()->with('error', 'Impossible de modifier le statut d\'un compte Direction');
        }
        
        try {
            // Si l'utilisateur n'a pas de colonne 'active', on peut simuler avec 'email_verified_at'
            // Ou ajouter une colonne 'active' à la migration
            $user->update([
                'email_verified_at' => $user->email_verified_at ? null : now()
            ]);
            
            $status = $user->email_verified_at ? 'activé' : 'désactivé';
            return back()->with('success', "Utilisateur {$status} avec succès");
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la modification du statut');
        }
    }

    public function assignDepartment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id'
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $user->update(['department_id' => $request->department_id]);
            
            return back()->with('success', 'Département assigné avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'assignation du département');
        }
    }
}