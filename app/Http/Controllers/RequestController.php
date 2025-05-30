<?php
namespace App\Http\Controllers;
//app/Http/Controllers/RequestController.php

use Illuminate\Http\Request;
use App\Models\Request as DepartmentRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function index()
    {
        // CORRECTION : Utiliser l'utilisateur connecté
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        if (!$departmentId) {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }
        
        $requests = DepartmentRequest::where('department_id', $departmentId)
                                 ->with(['user', 'approver'])
                                 ->orderBy('created_at', 'desc')
                                 ->get();
        
        return view('requests.index', compact('requests'));
    }
    
    public function show($id)
    {
        $request = DepartmentRequest::with(['user', 'approver'])->findOrFail($id);
        return view('requests.show', compact('request'));
    }
    
    public function approve($id)
    {
        // CORRECTION : Utiliser l'utilisateur connecté
        $departmentHead = Auth::user();
        
        $request = DepartmentRequest::findOrFail($id);
        
        $request->update([
            'status' => 'approved',
            'approved_by' => $departmentHead->id,
            'approved_at' => now(),
        ]);
        
        return redirect()->route('requests.index')
                         ->with('success', 'Request approved successfully');
    }
    
    public function reject($id)
    {
        // CORRECTION : Utiliser l'utilisateur connecté
        $departmentHead = Auth::user();
        
        $request = DepartmentRequest::findOrFail($id);
        
        $request->update([
            'status' => 'rejected',
            'approved_by' => $departmentHead->id,
            'approved_at' => now(),
        ]);
        
        return redirect()->route('requests.index')
                         ->with('success', 'Request rejected successfully');
    }
}