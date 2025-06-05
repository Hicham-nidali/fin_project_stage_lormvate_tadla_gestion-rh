<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        
        $tasks = $tasksQuery->orderBy('due_date')->get();
        
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
        
        // Validation pour les tâches terminées
        if ($newStatus == 'completed') {
            $request->validate([
                'completion_notes' => 'nullable|string|max:1000',
                'completion_proof' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240' // 10MB max
            ], [
                'completion_proof.required' => 'Une preuve de completion est requise pour terminer la tâche.',
                'completion_proof.mimes' => 'Le fichier doit être une image (jpg, jpeg, png), un PDF ou un document Word.',
                'completion_proof.max' => 'Le fichier ne peut pas dépasser 10 MB.'
            ]);
        } else {
            $request->validate([
                'completion_notes' => 'nullable|string|max:1000'
            ]);
        }
        
        if (in_array($newStatus, ['in_progress', 'completed'])) {
            // Gérer l'upload du fichier de preuve pour les tâches terminées
            $proofPath = null;
            if ($newStatus == 'completed' && $request->hasFile('completion_proof')) {
                $file = $request->file('completion_proof');
                $filename = time() . '_' . $employee->id . '_' . $file->getClientOriginalName();
                $proofPath = $file->storeAs('task_proofs', $filename, 'public');
            }
            
            // Supprimer l'ancien fichier de preuve s'il existe
            if ($task->completion_proof && $proofPath) {
                $task->deleteCompletionProof();
            }
            
            $task->status = $newStatus;
            $task->completion_notes = $request->input('completion_notes');
            
            if ($newStatus == 'completed') {
                $task->completed_at = now();
                if ($proofPath) {
                    $task->completion_proof = $proofPath;
                }
            }
            
            $task->save();
            
            $message = $newStatus == 'completed' 
                ? 'Tâche marquée comme terminée avec succès. Votre preuve a été envoyée au chef de département.'
                : 'Statut de la tâche mis à jour avec succès.';
            
            return redirect()->route('employee.tasks.show', $task->id)
                             ->with('success', $message);
        }
        
        return redirect()->route('employee.tasks.show', $task->id)
                         ->with('error', 'Statut invalide.');
    }
}