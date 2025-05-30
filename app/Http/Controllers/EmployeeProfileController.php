<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Auth;

class EmployeeProfileController extends Controller
{
    public function index()
    {
        $employee = Auth::user();
        
        if (!$employee || $employee->role !== 'employee') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $evaluations = Evaluation::where('evaluated_user_id', $employee->id)
                                ->orderBy('created_at', 'desc')
                                ->get();
        
        return view('employee.profile.index', compact('employee', 'evaluations'));
    }
    
    public function edit()
    {
        $employee = Auth::user();
        
        if (!$employee || $employee->role !== 'employee') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        return view('employee.profile.edit', compact('employee'));
    }
    
    public function update(Request $request)
    {
        $employee = Auth::user();
        
        if (!$employee || $employee->role !== 'employee') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $employee->id,
        ]);
        
        $employee->name = $request->name;
        $employee->email = $request->email;
        // Autres champs à mettre à jour
        
        $employee->save();
        
        return redirect()->route('employee.profile.index')
                         ->with('success', 'Profil mis à jour avec succès.');
    }
}