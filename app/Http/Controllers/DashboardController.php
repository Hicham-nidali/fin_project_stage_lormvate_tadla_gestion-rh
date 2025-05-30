<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User;
use App\Models\Task;
use App\Models\Attendance;
use App\Models\Request as DepartmentRequest;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // CORRECTION : Utiliser l'utilisateur connecté au lieu de simuler
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        if (!$departmentId) {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }
        
        $department = Department::findOrFail($departmentId);
        $totalEmployees = User::where('department_id', $departmentId)->count();
        $pendingTasks = Task::where('department_id', $departmentId)
                            ->where('status', 'pending')
                            ->count();
        $pendingRequests = DepartmentRequest::where('department_id', $departmentId)
                                 ->where('status', 'pending')
                                 ->count();
        
        return view('dashboard', compact(
            'department', 
            'totalEmployees', 
            'pendingTasks', 
            'pendingRequests'
        ));
    }
}