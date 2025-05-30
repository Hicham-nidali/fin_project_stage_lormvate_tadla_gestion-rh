<?php
// app/Http/Controllers/TaskController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        // CORRECTION : Utiliser l'utilisateur connecté
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        if (!$departmentId) {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }
        
        $tasks = Task::where('department_id', $departmentId)
                    ->with(['assignedTo', 'assignedBy'])
                    ->orderBy('due_date')
                    ->get();
        
        return view('tasks.index', compact('tasks'));
    }
    
    public function create()
    {
        // CORRECTION : Utiliser l'utilisateur connecté
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        if (!$departmentId) {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }
        
        $team = User::where('department_id', $departmentId)->get();
        
        return view('tasks.create', compact('team'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'required|date',
        ]);
        
        // CORRECTION : Utiliser l'utilisateur connecté
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'assigned_by' => $departmentHead->id,
            'department_id' => $departmentId,
            'status' => 'pending',
            'priority' => $request->priority,
            'due_date' => $request->due_date,
        ]);
        
        return redirect()->route('tasks.index')
                         ->with('success', 'Task created successfully');
    }
    
    public function show($id)
    {
        $task = Task::with(['assignedTo', 'assignedBy'])->findOrFail($id);
        return view('tasks.show', compact('task'));
    }
    
    public function edit($id)
    {
        // CORRECTION : Utiliser l'utilisateur connecté
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        $task = Task::findOrFail($id);
        $team = User::where('department_id', $departmentId)->get();
        
        return view('tasks.edit', compact('task', 'team'));
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'assigned_to' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'due_date' => 'required|date',
        ]);
        
        $task = Task::findOrFail($id);
        
        $task->update($request->all());
        
        if ($request->status == 'completed' && !$task->completed_at) {
            $task->completed_at = now();
            $task->save();
        }
        
        return redirect()->route('tasks.index')
                         ->with('success', 'Task updated successfully');
    }
}