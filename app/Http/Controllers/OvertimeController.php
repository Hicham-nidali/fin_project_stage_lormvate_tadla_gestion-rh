<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OvertimeRecord;
use App\Models\Request as EmployeeRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        if (!$departmentId) {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }
        
        $status = $request->input('status');
        
        $overtimeQuery = OvertimeRecord::where('department_id', $departmentId)
                                      ->with(['user', 'request']);
        
        if ($status) {
            $overtimeQuery->where('status', $status);
        }
        
        $overtimeRecords = $overtimeQuery->orderBy('created_at', 'desc')->get();
        
        return view('overtime.index', compact('overtimeRecords', 'status'));
    }
    
    public function show($id)
    {
        $overtimeRecord = OvertimeRecord::with(['user', 'request', 'approver'])->findOrFail($id);
        
        $departmentHead = Auth::user();
        if ($overtimeRecord->department_id !== $departmentHead->department_id) {
            return redirect()->route('overtime.index')->with('error', 'Accès refusé.');
        }
        
        return view('overtime.show', compact('overtimeRecord'));
    }
    
    public function approve($id)
    {
        $departmentHead = Auth::user();
        $overtimeRecord = OvertimeRecord::findOrFail($id);
        
        if ($overtimeRecord->department_id !== $departmentHead->department_id) {
            return redirect()->route('overtime.index')->with('error', 'Accès refusé.');
        }
        
        $overtimeRecord->update([
            'status' => 'approved',
            'hours_approved' => $overtimeRecord->hours_requested,
            'approved_by' => $departmentHead->id,
            'approved_at' => now(),
        ]);
        
        $employeeRequest = EmployeeRequest::find($overtimeRecord->request_id);
        if ($employeeRequest) {
            $employeeRequest->update([
                'status' => 'approved',
                'approved_by' => $departmentHead->id,
                'approved_at' => now(),
            ]);
        }
        
        return redirect()->route('overtime.index')
                         ->with('success', 'Heures supplémentaires approuvées avec succès.');
    }
    
    public function reject($id)
    {
        $departmentHead = Auth::user();
        $overtimeRecord = OvertimeRecord::findOrFail($id);
        
        if ($overtimeRecord->department_id !== $departmentHead->department_id) {
            return redirect()->route('overtime.index')->with('error', 'Accès refusé.');
        }
        
        $overtimeRecord->update([
            'status' => 'rejected',
            'approved_by' => $departmentHead->id,
            'approved_at' => now(),
        ]);
        
        $employeeRequest = EmployeeRequest::find($overtimeRecord->request_id);
        if ($employeeRequest) {
            $employeeRequest->update([
                'status' => 'rejected',
                'approved_by' => $departmentHead->id,
                'approved_at' => now(),
            ]);
        }
        
        return redirect()->route('overtime.index')
                         ->with('success', 'Heures supplémentaires rejetées.');
    }
    
    public function report(Request $request)
    {
        $departmentHead = Auth::user();
        $departmentId = $departmentHead->department_id;
        
        if (!$departmentId) {
            return redirect()->route('login')->with('error', 'Vous n\'êtes pas assigné à un département.');
        }
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        
        $overtimeData = OvertimeRecord::where('department_id', $departmentId)
                                     ->where('status', 'approved')
                                     ->whereBetween('overtime_date', [$startDate, $endDate])
                                     ->with(['user'])
                                     ->get();
        
        $overtimeByEmployee = $overtimeData->groupBy('user_id')->map(function ($records) {
            $user = $records->first()->user;
            $totalHours = $records->sum('hours_approved');
            $totalRecords = $records->count();
            
            return [
                'user' => $user,
                'total_hours' => $totalHours,
                'total_records' => $totalRecords,
                'records' => $records
            ];
        });
        
        $totalOvertimeHours = $overtimeData->sum('hours_approved');
        $totalOvertimeRequests = $overtimeData->count();
        $averageHoursPerRequest = $totalOvertimeRequests > 0 ? $totalOvertimeHours / $totalOvertimeRequests : 0;
        
        return view('overtime.report', compact(
            'overtimeByEmployee', 
            'startDate', 
            'endDate',
            'totalOvertimeHours',
            'totalOvertimeRequests',
            'averageHoursPerRequest'
        ));
    }
}