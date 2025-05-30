<?php
namespace App\Http\Controllers;
//app/Http/Controllers/ReportController.php

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\User;
use App\Models\Task;
use App\Models\Attendance;
use App\Models\Evaluation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        // CORRECTION : Utiliser l'utilisateur connecté
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        if (!$departmentId) {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }
        
        $reports = Report::where('department_id', $departmentId)
                        ->with('creator')
                        ->orderBy('created_at', 'desc')
                        ->get();
        
        return view('reports.index', compact('reports'));
    }
    
    public function create()
    {
        return view('reports.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'type' => 'required|in:monthly,quarterly,annual,custom',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'status' => 'required|in:draft,published',
        ]);
        
        // CORRECTION : Utiliser l'utilisateur connecté
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        Report::create([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'department_id' => $departmentId,
            'created_by' => $departmentHead->id,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'status' => $request->status,
        ]);
        
        return redirect()->route('reports.index')
                         ->with('success', 'Report created successfully');
    }
    
    public function show($id)
    {
        $report = Report::with('creator', 'department')->findOrFail($id);
        return view('reports.show', compact('report'));
    }
    
    public function edit($id)
    {
        $report = Report::findOrFail($id);
        return view('reports.edit', compact('report'));
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'type' => 'required|in:monthly,quarterly,annual,custom',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'status' => 'required|in:draft,published',
        ]);
        
        $report = Report::findOrFail($id);
        $report->update($request->all());
        
        return redirect()->route('reports.index')
                         ->with('success', 'Report updated successfully');
    }
    
    public function generateMonthlyReport()
    {
        // CORRECTION : Utiliser l'utilisateur connecté
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        if (!$departmentId) {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }
        
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        // Get team performance data
        $teamPerformance = $this->getTeamPerformanceData($departmentId, $startDate, $endDate);
        
        return view('reports.generate-monthly', compact('teamPerformance', 'startDate', 'endDate'));
    }
    
    private function getTeamPerformanceData($departmentId, $startDate, $endDate)
    {
        // Get tasks completed
        $tasksCompleted = Task::where('department_id', $departmentId)
                             ->where('status', 'completed')
                             ->whereBetween('completed_at', [$startDate, $endDate])
                             ->count();
        
        // Get attendance data
        $attendanceData = Attendance::whereHas('user', function($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        $totalAttendance = $attendanceData->count();
        $presentCount = $attendanceData->where('status', 'present')->count();
        $absentCount = $attendanceData->where('status', 'absent')->count();
        $lateCount = $attendanceData->where('status', 'late')->count();
        
        // Get total employees
        $totalEmployees = User::where('department_id', $departmentId)->count();
        
        return [
            'tasks_completed' => $tasksCompleted,
            'total_attendance' => $totalAttendance,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
            'total_employees' => $totalEmployees,
        ];
    }
}