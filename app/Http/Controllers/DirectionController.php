<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DirectionController extends Controller
{
    public function dashboard()
    {
        // Statistiques globales simples
        $totalUsers = User::whereIn('role', ['employee', 'department_head', 'hr_admin'])->count();
        
        // Présences du jour
        $today = Carbon::today()->toDateString();
        $todayAttendances = Attendance::where('date', $today)->count();
        $presentToday = Attendance::where('date', $today)->where('status', 'present')->count();
        $absentToday = Attendance::where('date', $today)->where('status', 'absent')->count();
        $lateToday = Attendance::where('date', $today)->where('status', 'late')->count();
        $notRecorded = $totalUsers - $todayAttendances;

        return view('direction.dashboard', compact(
            'totalUsers',
            'presentToday',
            'absentToday',
            'lateToday',
            'notRecorded'
        ));
    }

    public function attendance(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        
        // Toutes les présences pour la date sélectionnée
        $attendances = Attendance::with(['user.department'])
            ->where('date', $date)
            ->get();
        
        // Utilisateurs sans présence enregistrée
        $usersWithoutAttendance = User::with('department')
            ->whereIn('role', ['employee', 'department_head', 'hr_admin'])
            ->whereNotIn('id', Attendance::where('date', $date)->pluck('user_id'))
            ->get();
        
        // Statistiques
        $totalUsers = User::whereIn('role', ['employee', 'department_head', 'hr_admin'])->count();
        $totalPresent = $attendances->where('status', 'present')->count();
        $totalAbsent = $attendances->where('status', 'absent')->count();
        $totalLate = $attendances->where('status', 'late')->count();
        $totalNotRecorded = $usersWithoutAttendance->count();

        return view('direction.attendance', compact(
            'attendances',
            'usersWithoutAttendance', 
            'date',
            'totalUsers',
            'totalPresent',
            'totalAbsent',
            'totalLate',
            'totalNotRecorded'
        ));
    }

    public function attendanceReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        // Rapport de présence global par période
        $attendanceData = Attendance::with(['user.department'])
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        // Statistiques globales pour la période
        $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $totalUsers = User::whereIn('role', ['employee', 'department_head', 'hr_admin'])->count();
        $totalPossibleAttendances = $totalUsers * $totalDays;
        $totalRecordedAttendances = $attendanceData->count();
        $globalAttendanceRate = $totalPossibleAttendances > 0 ? round(($totalRecordedAttendances / $totalPossibleAttendances) * 100, 1) : 0;
        
        return view('direction.attendance-report', compact(
            'attendanceData',
            'startDate',
            'endDate',
            'totalDays',
            'globalAttendanceRate',
            'totalUsers'
        ));
    }
}