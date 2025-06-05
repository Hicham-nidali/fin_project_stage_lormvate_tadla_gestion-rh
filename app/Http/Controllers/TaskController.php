<?php
// app/Http/Controllers/TaskController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class TaskController extends Controller
{
    public function index()
    {
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
                         ->with('success', 'Tâche créée avec succès');
    }
    
    public function show($id)
    {
        $task = Task::with(['assignedTo', 'assignedBy'])->findOrFail($id);
        
        // Vérifier que la tâche appartient au département du chef connecté
        $departmentHead = Auth::user();
        if ($task->department_id !== $departmentHead->department_id) {
            return redirect()->route('tasks.index')
                             ->with('error', 'Vous n\'avez pas accès à cette tâche.');
        }
        
        return view('tasks.show', compact('task'));
    }
    
    public function edit($id)
    {
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        $task = Task::findOrFail($id);
        
        // Vérifier que la tâche appartient au département du chef connecté
        if ($task->department_id !== $departmentId) {
            return redirect()->route('tasks.index')
                             ->with('error', 'Vous n\'avez pas accès à cette tâche.');
        }
        
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
        
        // Vérifier que la tâche appartient au département du chef connecté
        $departmentHead = Auth::user();
        if ($task->department_id !== $departmentHead->department_id) {
            return redirect()->route('tasks.index')
                             ->with('error', 'Vous n\'avez pas accès à cette tâche.');
        }
        
        $task->update($request->all());
        
        if ($request->status == 'completed' && !$task->completed_at) {
            $task->completed_at = now();
            $task->save();
        }
        
        return redirect()->route('tasks.index')
                         ->with('success', 'Tâche mise à jour avec succès');
    }

    /**
     * Valider une tâche terminée avec preuve
     */
    public function validateCompletion(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        
        // Vérifier que la tâche appartient au département du chef connecté
        $departmentHead = Auth::user();
        if ($task->department_id !== $departmentHead->department_id) {
            return redirect()->route('tasks.index')
                             ->with('error', 'Vous n\'avez pas accès à cette tâche.');
        }
        
        $request->validate([
            'validation' => 'required|in:approved,rejected',
            'feedback' => 'nullable|string|max:1000'
        ]);
        
        if ($request->validation === 'approved') {
            $task->update([
                'status' => 'completed',
                'completion_notes' => $task->completion_notes . "\n\nValidé par: " . $departmentHead->name . 
                                    ($request->feedback ? "\nCommentaire: " . $request->feedback : "")
            ]);
            
            $message = 'Tâche validée avec succès.';
        } else {
            $task->update([
                'status' => 'in_progress',
                'completion_notes' => $task->completion_notes . "\n\nRejetée par: " . $departmentHead->name . 
                                    ($request->feedback ? "\nRaison: " . $request->feedback : "")
            ]);
            
            $message = 'Tâche renvoyée à l\'employé pour correction.';
        }
        
        return redirect()->route('tasks.show', $task->id)
                         ->with('success', $message);
    }

    /**
     * NOUVEAU : Afficher le fichier de preuve
     */
    public function viewProof($id)
    {
        $task = Task::findOrFail($id);
        
        // Vérifier les permissions
        $user = Auth::user();
        if ($task->department_id !== $user->department_id && $task->assigned_to !== $user->id) {
            abort(403, 'Accès non autorisé');
        }

        if (!$task->hasCompletionProof()) {
            abort(404, 'Aucune preuve trouvée');
        }

        $filePath = storage_path('app/public/' . $task->completion_proof);
        
        if (!file_exists($filePath)) {
            abort(404, 'Fichier non trouvé');
        }

        $mimeType = Storage::disk('public')->mimeType($task->completion_proof);
        
        return Response::file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $task->completion_proof_name . '"'
        ]);
    }

    /**
     * NOUVEAU : Télécharger le fichier de preuve
     */
    public function downloadProof($id)
    {
        $task = Task::findOrFail($id);
        
        // Vérifier les permissions
        $user = Auth::user();
        if ($task->department_id !== $user->department_id && $task->assigned_to !== $user->id) {
            abort(403, 'Accès non autorisé');
        }

        if (!$task->hasCompletionProof()) {
            abort(404, 'Aucune preuve trouvée');
        }

        $filePath = storage_path('app/public/' . $task->completion_proof);
        
        if (!file_exists($filePath)) {
            abort(404, 'Fichier non trouvé');
        }

        return response()->download($filePath, $task->completion_proof_name);
    }
}