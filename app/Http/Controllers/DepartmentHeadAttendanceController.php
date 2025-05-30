<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DepartmentHeadAttendanceController extends Controller
{
    public function index()
    {
        $departmentHead = Auth::user();
        
        if (!$departmentHead || $departmentHead->role !== 'department_head') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $todayAttendance = Attendance::where('user_id', $departmentHead->id)
                                    ->where('date', now()->toDateString())
                                    ->first();
        
        return view('department-head.attendance.index', compact('departmentHead', 'todayAttendance'));
    }
    
    public function checkIn(Request $request)
    {
        $departmentHead = Auth::user();
        
        if (!$departmentHead || $departmentHead->role !== 'department_head') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $todayAttendance = Attendance::where('user_id', $departmentHead->id)
                                    ->where('date', now()->toDateString())
                                    ->first();
        
        if (!$todayAttendance) {
            $todayAttendance = new Attendance();
            $todayAttendance->user_id = $departmentHead->id;
            $todayAttendance->date = now()->toDateString();
            $todayAttendance->status = 'present';
        }
        
        $todayAttendance->check_in = now();
        $todayAttendance->save();
        
        return redirect()->route('department-head.attendance.index')
                         ->with('success', 'Vous avez pointé votre arrivée avec succès.');
    }
    
    public function checkOut(Request $request)
    {
        $departmentHead = Auth::user();
        
        if (!$departmentHead || $departmentHead->role !== 'department_head') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $todayAttendance = Attendance::where('user_id', $departmentHead->id)
                                    ->where('date', now()->toDateString())
                                    ->first();
        
        if ($todayAttendance && $todayAttendance->check_in) {
            $todayAttendance->check_out = now();
            $todayAttendance->save();
            
            return redirect()->route('department-head.attendance.index')
                             ->with('success', 'Vous avez pointé votre départ avec succès.');
        }
        
        return redirect()->route('department-head.attendance.index')
                         ->with('error', 'Vous devez d\'abord pointer votre arrivée.');
    }
    
    public function history(Request $request)
    {
        $departmentHead = Auth::user();
        
        if (!$departmentHead || $departmentHead->role !== 'department_head') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        $attendanceHistory = Attendance::where('user_id', $departmentHead->id)
                                      ->whereBetween('date', [$startDate, $endDate])
                                      ->orderBy('date', 'desc')
                                      ->get();
        
        return view('department-head.attendance.history', compact('departmentHead', 'attendanceHistory', 'startDate', 'endDate'));
    }
}