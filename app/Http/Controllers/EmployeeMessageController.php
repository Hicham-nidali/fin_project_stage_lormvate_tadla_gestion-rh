<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class EmployeeMessageController extends Controller
{
    public function index()
    {
        $employee = Auth::user();
        
        if (!$employee || $employee->role !== 'employee') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $messages = Message::where('department_id', $employee->department_id)
                          ->orWhere('user_id', $employee->id)
                          ->orderBy('created_at', 'desc')
                          ->get();
        
        return view('employee.messages.index', compact('employee', 'messages'));
    }
    
    public function show($id)
    {
        $employee = Auth::user();
        
        if (!$employee || $employee->role !== 'employee') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $message = Message::findOrFail($id);
        
        // Vérifier si le message est destiné à cet employé ou à son département
        if ($message->user_id != $employee->id && $message->department_id != $employee->department_id) {
            return redirect()->route('employee.messages.index')
                             ->with('error', 'Vous n\'êtes pas autorisé à voir ce message.');
        }
        
        // Marquer comme lu si ce n'est pas déjà fait
        if (!$message->read_at && $message->user_id == $employee->id) {
            $message->read_at = now();
            $message->save();
        }
        
        return view('employee.messages.show', compact('employee', 'message'));
    }
}