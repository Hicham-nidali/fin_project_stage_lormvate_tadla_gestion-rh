<?php
namespace App\Http\Controllers;
// app/Http/Controllers/EvaluationController.php

use Illuminate\Http\Request;
use App\Models\Evaluation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    public function index()
    {
        // CORRECTION : Utiliser l'utilisateur connecté
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        if (!$departmentId) {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }
        
        $evaluations = Evaluation::whereHas('evaluatedUser', function($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->with(['evaluatedUser', 'evaluator'])
            ->get();
        
        return view('evaluations.index', compact('evaluations'));
    }
    
    public function create(Request $request)
    {
        // CORRECTION : Utiliser l'utilisateur connecté
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        if (!$departmentId) {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }
        
        $userId = $request->input('user_id');
        $user = null;
        
        if ($userId) {
            $user = User::where('id', $userId)
                       ->where('department_id', $departmentId)
                       ->first();
        }
        
        $team = User::where('department_id', $departmentId)
                    ->where('id', '!=', $departmentHead->id)
                    ->get();
        
        return view('evaluations.create', compact('team', 'user'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'evaluated_user_id' => 'required|exists:users,id',
            'period' => 'required',
            'performance_score' => 'required|integer|min:1|max:10',
            'communication_score' => 'required|integer|min:1|max:10',
            'teamwork_score' => 'required|integer|min:1|max:10',
            'innovation_score' => 'required|integer|min:1|max:10',
            'comments' => 'required',
            'status' => 'required|in:draft,published',
        ]);
        
        // CORRECTION : Utiliser l'utilisateur connecté
        $departmentHead = Auth::user();
        
        Evaluation::create([
            'evaluated_user_id' => $request->evaluated_user_id,
            'evaluator_id' => $departmentHead->id,
            'period' => $request->period,
            'performance_score' => $request->performance_score,
            'communication_score' => $request->communication_score,
            'teamwork_score' => $request->teamwork_score,
            'innovation_score' => $request->innovation_score,
            'comments' => $request->comments,
            'status' => $request->status,
        ]);
        
        return redirect()->route('evaluations.index')
                         ->with('success', 'Evaluation created successfully');
    }
    
    public function show($id)
    {
        $evaluation = Evaluation::with(['evaluatedUser', 'evaluator'])->findOrFail($id);
        return view('evaluations.show', compact('evaluation'));
    }
    
    public function edit($id)
    {
        $evaluation = Evaluation::findOrFail($id);
        return view('evaluations.edit', compact('evaluation'));
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'period' => 'required',
            'performance_score' => 'required|integer|min:1|max:10',
            'communication_score' => 'required|integer|min:1|max:10',
            'teamwork_score' => 'required|integer|min:1|max:10',
            'innovation_score' => 'required|integer|min:1|max:10',
            'comments' => 'required',
            'status' => 'required|in:draft,published',
        ]);
        
        $evaluation = Evaluation::findOrFail($id);
        $evaluation->update($request->all());
        
        return redirect()->route('evaluations.index')
                         ->with('success', 'Evaluation updated successfully');
    }
}