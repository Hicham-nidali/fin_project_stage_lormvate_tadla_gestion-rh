<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HRAttendanceController extends Controller
{
    public function index()
    {
        $hrAdmin = Auth::user();
        
        if (!$hrAdmin || $hrAdmin->role !== 'hr_admin') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $todayAttendance = Attendance::where('user_id', $hrAdmin->id)
                                    ->where('date', now()->toDateString())
                                    ->first();
        
        return view('hr.attendance.index', compact('hrAdmin', 'todayAttendance'));
    }
    
    public function checkIn(Request $request)
    {
        $hrAdmin = Auth::user();
        
        if (!$hrAdmin || $hrAdmin->role !== 'hr_admin') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $todayAttendance = Attendance::where('user_id', $hrAdmin->id)
                                    ->where('date', now()->toDateString())
                                    ->first();
        
        if (!$todayAttendance) {
            $todayAttendance = new Attendance();
            $todayAttendance->user_id = $hrAdmin->id;
            $todayAttendance->date = now()->toDateString();
            $todayAttendance->status = 'present';
        }
        
        $todayAttendance->check_in = now();
        $todayAttendance->save();
        
        return redirect()->route('hr.attendance.index')
                         ->with('success', 'Vous avez pointé votre arrivée avec succès.');
    }
    
    public function checkOut(Request $request)
    {
        $hrAdmin = Auth::user();
        
        if (!$hrAdmin || $hrAdmin->role !== 'hr_admin') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $todayAttendance = Attendance::where('user_id', $hrAdmin->id)
                                    ->where('date', now()->toDateString())
                                    ->first();
        
        if ($todayAttendance && $todayAttendance->check_in) {
            $todayAttendance->check_out = now();
            $todayAttendance->save();
            
            return redirect()->route('hr.attendance.index')
                             ->with('success', 'Vous avez pointé votre départ avec succès.');
        }
        
        return redirect()->route('hr.attendance.index')
                         ->with('error', 'Vous devez d\'abord pointer votre arrivée.');
    }
    
    public function history(Request $request)
    {
        $hrAdmin = Auth::user();
        
        if (!$hrAdmin || $hrAdmin->role !== 'hr_admin') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        $attendanceHistory = Attendance::where('user_id', $hrAdmin->id)
                                      ->whereBetween('date', [$startDate, $endDate])
                                      ->orderBy('date', 'desc')
                                      ->get();
        
        return view('hr.attendance.history', compact('hrAdmin', 'attendanceHistory', 'startDate', 'endDate'));
    }
}