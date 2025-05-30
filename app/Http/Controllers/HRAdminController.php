<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HRAdminController extends Controller
{
    // ==================== DASHBOARD ====================
    public function dashboard()
    {
        $totalDepartments = Department::count();
        $totalEmployees = User::where('role', 'employee')->count();
        $totalHeads = User::where('role', 'department_head')->count();
        $unassignedEmployees = User::whereNull('department_id')->count();
        $departments = Department::with(['users', 'head'])->get();
        
        // Statistiques pour les graphiques
        $departmentStats = Department::withCount('users')->get();
        $roleStats = User::select('role', DB::raw('count(*) as total'))
                        ->groupBy('role')
                        ->get();
        
        return view('hr.dashboard', compact(
            'totalDepartments', 'totalEmployees', 'totalHeads', 
            'unassignedEmployees', 'departments', 'departmentStats', 'roleStats'
        ));
    }

    // ==================== DÉPARTEMENTS ====================
    public function index()
    {
        return $this->departmentsIndex();
    }

    public function departmentsIndex()
    {
        $departments = Department::with(['users', 'head'])->get();
        return view('hr.departments.index', compact('departments'));
    }

    public function create()
    {
        return $this->departmentsCreate();
    }

    public function departmentsCreate()
    {
        $availableHeads = User::where('role', 'department_head')
                             ->whereDoesntHave('headedDepartment')
                             ->get();
        return view('hr.departments.create', compact('availableHeads'));
    }

    public function store(Request $request)
    {
        return $this->departmentsStore($request);
    }

    public function departmentsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:departments,name|max:255',
            'description' => 'nullable|max:1000',
            'head_id' => 'nullable|exists:users,id'
        ]);

        try {
            DB::beginTransaction();
            
            $department = Department::create([
                'name' => $request->name,
                'description' => $request->description,
                'head_id' => $request->head_id
            ]);
            
            // Si un chef est assigné, mettre à jour son département
            if ($request->head_id) {
                User::where('id', $request->head_id)
                    ->update(['department_id' => $department->id]);
            }
            
            DB::commit();
            return redirect()->route('hr.departments.index')
                           ->with('success', 'Département créé avec succès');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors de la création du département')
                        ->withInput();
        }
    }

    public function show($id)
    {
        return $this->departmentsShow($id);
    }

    public function departmentsShow($id)
    {
        $department = Department::with(['users', 'head'])->findOrFail($id);
        $unassignedEmployees = User::whereNull('department_id')
                                  ->where('role', '!=', 'hr_admin')
                                  ->get();
        $availableHeads = User::where('role', 'department_head')
                             ->where(function($query) use ($department) {
                                 $query->whereDoesntHave('headedDepartment')
                                       ->orWhere('id', $department->head_id);
                             })->get();
        
        return view('hr.departments.show', compact('department', 'unassignedEmployees', 'availableHeads'));
    }

    public function edit($id)
    {
        return $this->departmentsEdit($id);
    }

    public function departmentsEdit($id)
    {
        $department = Department::findOrFail($id);
        $availableHeads = User::where('role', 'department_head')
                             ->where(function($query) use ($department) {
                                 $query->whereDoesntHave('headedDepartment')
                                       ->orWhere('id', $department->head_id);
                             })->get();
        
        return view('hr.departments.edit', compact('department', 'availableHeads'));
    }

    public function update(Request $request, $id)
    {
        return $this->departmentsUpdate($request, $id);
    }

    public function departmentsUpdate(Request $request, $id)
    {
        $department = Department::findOrFail($id);
        
        $request->validate([
            'name' => ['required', 'max:255', Rule::unique('departments')->ignore($department->id)],
            'description' => 'nullable|max:1000',
            'head_id' => 'nullable|exists:users,id'
        ]);

        try {
            DB::beginTransaction();
            
            $oldHeadId = $department->head_id;
            
            $department->update([
                'name' => $request->name,
                'description' => $request->description,
                'head_id' => $request->head_id
            ]);
            
            // Gestion des changements de chef
            if ($oldHeadId != $request->head_id) {
                // Retirer l'ancien chef du département
                if ($oldHeadId) {
                    User::where('id', $oldHeadId)->update(['department_id' => null]);
                }
                
                // Assigner le nouveau chef au département
                if ($request->head_id) {
                    User::where('id', $request->head_id)
                        ->update(['department_id' => $department->id]);
                }
            }
            
            DB::commit();
            return redirect()->route('hr.departments.index')
                           ->with('success', 'Département modifié avec succès');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors de la modification du département')
                        ->withInput();
        }
    }

    public function destroy($id)
    {
        return $this->departmentsDestroy($id);
    }

    public function departmentsDestroy($id)
    {
        $department = Department::findOrFail($id);
        
        if ($department->users()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer un département contenant des employés');
        }
        
        try {
            $department->delete();
            return redirect()->route('hr.departments.index')
                           ->with('success', 'Département supprimé avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression du département');
        }
    }

    // ==================== UTILISATEURS ====================
    public function usersIndex()
    {
        $users = User::with('department')->where('role', '!=', 'hr_admin')->get();
        $departments = Department::all();
        return view('hr.users.index', compact('users', 'departments'));
    }

    public function usersCreate()
    {
        $departments = Department::all();
        return view('hr.users.create', compact('departments'));
    }

    public function usersStore(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:employee,department_head',
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
            return redirect()->route('hr.users.index')
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
        return view('hr.users.edit', compact('user', 'departments'));
    }

    public function usersUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
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
                Department::where('id', $request->department_id)
                          ->update(['head_id' => $user->id]);
            }
            
            DB::commit();
            return redirect()->route('hr.users.index')
                           ->with('success', 'Utilisateur modifié avec succès');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors de la modification de l\'utilisateur')
                        ->withInput();
        }
    }

    // ==================== ACTIONS D'ASSIGNATION ====================
    public function assignEmployee(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id'
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $user->update(['department_id' => $request->department_id]);
            
            return back()->with('success', 'Employé assigné avec succès au département');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'assignation de l\'employé');
        }
    }

    public function removeEmployee($userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            // Vérifier si c'est un chef de département
            if ($user->role == 'department_head') {
                Department::where('head_id', $userId)->update(['head_id' => null]);
            }
            
            $user->update(['department_id' => null]);
            
            return back()->with('success', 'Employé retiré du département avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du retrait de l\'employé');
        }
    }

    // ==================== STATISTIQUES ====================
    public function getStatistics()
    {
        return [
            'total_users' => User::count(),
            'total_departments' => Department::count(),
            'unassigned_users' => User::whereNull('department_id')->count(),
            'department_heads' => User::where('role', 'department_head')->count(),
            'employees' => User::where('role', 'employee')->count(),
        ];
    }
}