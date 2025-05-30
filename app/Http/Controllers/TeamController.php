<?php
namespace App\Http\Controllers;
// app/Http/Controllers/TeamController.php

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        // CORRECTION : Utiliser l'utilisateur connecté
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        if (!$departmentId) {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }
        
        $team = User::where('department_id', $departmentId)->get();
        
        return view('team.index', compact('team'));
    }
    
    public function show($id)
    {
        $member = User::findOrFail($id);
        return view('team.show', compact('member'));
    }
    
    public function edit($id)
    {
        $member = User::findOrFail($id);
        return view('team.edit', compact('member'));
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
        ]);
        
        $member = User::findOrFail($id);
        $member->update($request->all());
        
        return redirect()->route('team.index')
                         ->with('success', 'Member updated successfully');
    }
}