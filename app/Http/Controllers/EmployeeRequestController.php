<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as EmployeeRequest;
use App\Models\OvertimeRecord;
use Illuminate\Support\Facades\Auth;

class EmployeeRequestController extends Controller
{
    public function index()
    {
        $employee = Auth::user();
        
        if (!$employee || $employee->role !== 'employee') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $requests = EmployeeRequest::where('user_id', $employee->id)
                                ->orderBy('created_at', 'desc')
                                ->get();
        
        return view('employee.requests.index', compact('employee', 'requests'));
    }
    
    public function create()
    {
        $employee = Auth::user();
        
        if (!$employee || $employee->role !== 'employee') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        return view('employee.requests.create', compact('employee'));
    }
    
    public function store(Request $request)
    {
        $employee = Auth::user();
        
        if (!$employee || $employee->role !== 'employee') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        // Validation de base
        $validationRules = [
            'title' => 'required',
            'description' => 'required',
            'type' => 'required|in:leave,expense,equipment,overtime,other',
        ];
        
        // Validation spécifique aux heures supplémentaires
        if ($request->type === 'overtime') {
            $validationRules = array_merge($validationRules, [
                'overtime_date' => 'required|date',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
                'hours_requested' => 'required|numeric|min:0.5|max:12',
                'overtime_reason' => 'required',
                'overtime_type' => 'required|in:planned,urgent,project',
                'overtime_rate' => 'required|numeric|in:1.25,1.5,2'
            ]);
        }
        
        $request->validate($validationRules);
        
        // Créer la demande principale
        $employeeRequest = new EmployeeRequest();
        $employeeRequest->title = $request->title;
        $employeeRequest->description = $request->description;
        $employeeRequest->type = $request->type;
        $employeeRequest->status = 'pending';
        $employeeRequest->user_id = $employee->id;
        $employeeRequest->department_id = $employee->department_id;
        $employeeRequest->save();
        
        // Si c'est une demande d'heures supplémentaires, créer l'enregistrement overtime
        if ($request->type === 'overtime') {
            $overtimeRecord = new OvertimeRecord();
            $overtimeRecord->request_id = $employeeRequest->id;
            $overtimeRecord->user_id = $employee->id;
            $overtimeRecord->department_id = $employee->department_id;
            $overtimeRecord->overtime_date = $request->overtime_date;
            $overtimeRecord->start_time = $request->start_time;
            $overtimeRecord->end_time = $request->end_time;
            $overtimeRecord->hours_requested = $request->hours_requested;
            $overtimeRecord->reason = $request->overtime_reason;
            $overtimeRecord->status = 'pending';
            
            // Stocker les métadonnées dans le champ description de la demande principale
            $metadata = [
                'overtime_type' => $request->overtime_type,
                'overtime_rate' => $request->overtime_rate,
                'original_description' => $request->description
            ];
            $employeeRequest->description = json_encode($metadata);
            $employeeRequest->save();
            
            $overtimeRecord->save();
        }
        
        return redirect()->route('employee.requests.index')
                         ->with('success', 'Demande soumise avec succès.');
    }
    
    public function show($id)
    {
        $employee = Auth::user();
        
        if (!$employee || $employee->role !== 'employee') {
            return redirect()->route('login')->with('error', 'Accès refusé.');
        }
        
        $employeeRequest = EmployeeRequest::findOrFail($id);
        
        // Vérifier si la demande appartient à cet employé
        if ($employeeRequest->user_id != $employee->id) {
            return redirect()->route('employee.requests.index')
                             ->with('error', 'Vous n\'êtes pas autorisé à voir cette demande.');
        }
        
        // Charger les données d'heures supplémentaires si applicable
        $overtimeRecord = null;
        if ($employeeRequest->type === 'overtime') {
            $overtimeRecord = OvertimeRecord::where('request_id', $employeeRequest->id)->first();
        }
        
        return view('employee.requests.show', compact('employee', 'employeeRequest', 'overtimeRecord'));
    }
}