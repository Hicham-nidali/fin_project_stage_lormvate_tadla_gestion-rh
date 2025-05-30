<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class EmployeeTaskController extends Controller
{
    public function index(Request $request)
    {
        $employee = Auth::user();
        
        if (!$employee || $employee->role !== 'employee') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $status = $request->input('status');
        
        $tasksQuery = Task::where('assigned_to', $employee->id);
        
        if ($status) {
            $tasksQuery->where('status', $status);
        }
        
        $tasks = $tasksQuery->orderBy('due_date')
                          ->get();
        
        return view('employee.tasks.index', compact('employee', 'tasks', 'status'));
    }
    
    public function show($id)
    {
        $employee = Auth::user();
        
        if (!$employee || $employee->role !== 'employee') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $task = Task::findOrFail($id);
        
        // Vérifier si la tâche est assignée à cet employé
        if ($task->assigned_to != $employee->id) {
            return redirect()->route('employee.tasks.index')
                             ->with('error', 'Vous n\'êtes pas autorisé à voir cette tâche.');
        }
        
        return view('employee.tasks.show', compact('employee', 'task'));
    }
    
    public function updateStatus(Request $request, $id)
    {
        $employee = Auth::user();
        
        if (!$employee || $employee->role !== 'employee') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $task = Task::findOrFail($id);
        
        // Vérifier si la tâche est assignée à cet employé
        if ($task->assigned_to != $employee->id) {
            return redirect()->route('employee.tasks.index')
                             ->with('error', 'Vous n\'êtes pas autorisé à modifier cette tâche.');
        }
        
        $newStatus = $request->input('status');
        
        if (in_array($newStatus, ['in_progress', 'completed'])) {
            $task->status = $newStatus;
            
            if ($newStatus == 'completed') {
                $task->completed_at = now();
            }
            
            $task->save();
            
            return redirect()->route('employee.tasks.show', $task->id)
                             ->with('success', 'Statut de la tâche mis à jour avec succès.');
        }
        
        return redirect()->route('employee.tasks.show', $task->id)
                         ->with('error', 'Statut invalide.');
    }
}