<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Attendance;
use App\Models\Request as EmployeeRequest;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $employee = Auth::user();
        
        // Redirection automatique si pas employé
        if (!$employee || $employee->role !== 'employee') {
            return $this->redirectByRole($employee);
        }
        
        $pendingTasks = Task::where('assigned_to', $employee->id)
                           ->whereIn('status', ['pending', 'in_progress'])
                           ->count();
        
        $completedTasks = Task::where('assigned_to', $employee->id)
                             ->where('status', 'completed')
                             ->count();
        
        $pendingRequests = EmployeeRequest::where('user_id', $employee->id)
                                      ->where('status', 'pending')
                                      ->count();
        
        $todayAttendance = Attendance::where('user_id', $employee->id)
                                    ->where('date', now()->toDateString())
                                    ->first();
        
        $recentMessages = Message::where('department_id', $employee->department_id)
                                ->orWhere('user_id', $employee->id)
                                ->orderBy('created_at', 'desc')
                                ->take(5)
                                ->get();
        
        return view('employee.dashboard', compact(
            'employee', 
            'pendingTasks', 
            'completedTasks', 
            'pendingRequests', 
            'todayAttendance',
            'recentMessages'
        ));
    }
    
    private function redirectByRole($user)
    {
        if (!$user) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter');
        }
        
        switch ($user->role) {
            case 'hr_admin':
                return redirect()->route('hr.dashboard');
            case 'department_head':
                return redirect()->route('dashboard');
            default:
                return redirect()->route('login')->with('error', 'Rôle non reconnu');
        }
    }
}