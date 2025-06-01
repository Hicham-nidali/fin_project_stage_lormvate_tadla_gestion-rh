<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EvaluationReport;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class HREvaluationReportController extends Controller
{
    public function index()
    {
        $reports = EvaluationReport::with(['department', 'creator'])
                                 ->where('status', '!=', 'draft')
                                 ->orderBy('sent_at', 'desc')
                                 ->get();
        
        $departments = Department::all();
        
        return view('hr.evaluation-reports.index', compact('reports', 'departments'));
    }

    public function show($id)
    {
        $report = EvaluationReport::with(['department', 'creator', 'reviewer'])->findOrFail($id);
        
        // Seuls les rapports envoyés peuvent être vus par RH
        if ($report->status == 'draft') {
            abort(404);
        }
        
        return view('hr.evaluation-reports.show', compact('report'));
    }

    public function review($id)
    {
        $report = EvaluationReport::findOrFail($id);
        
        if ($report->status != 'sent') {
            return redirect()->route('hr.evaluation-reports.index')
                            ->with('error', 'Ce rapport a déjà été examiné');
        }
        
        return view('hr.evaluation-reports.review', compact('report'));
    }

    public function storeReview(Request $request, $id)
    {
        $request->validate([
            'hr_comments' => 'required|string',
        ]);

        $report = EvaluationReport::findOrFail($id);
        
        if ($report->status != 'sent') {
            return redirect()->route('hr.evaluation-reports.index')
                            ->with('error', 'Ce rapport a déjà été examiné');
        }

        $report->update([
            'status' => 'reviewed',
            'reviewed_by' => Auth::user()->id,
            'reviewed_at' => now(),
            'hr_comments' => $request->hr_comments,
        ]);

        return redirect()->route('hr.evaluation-reports.index')
                        ->with('success', 'Rapport examiné avec succès');
    }

    public function dashboard()
    {
        $totalReports = EvaluationReport::where('status', '!=', 'draft')->count();
        $pendingReviews = EvaluationReport::where('status', 'sent')->count();
        $reviewedReports = EvaluationReport::where('status', 'reviewed')->count();
        
        $recentReports = EvaluationReport::with(['department', 'creator'])
                                       ->where('status', 'sent')
                                       ->orderBy('sent_at', 'desc')
                                       ->take(5)
                                       ->get();

        $departmentStats = Department::withCount(['evaluationReports as reports_count' => function($query) {
            $query->where('status', '!=', 'draft');
        }])->get();

        return view('hr.evaluation-reports.dashboard', compact(
            'totalReports', 
            'pendingReviews', 
            'reviewedReports', 
            'recentReports',
            'departmentStats'
        ));
    }
}