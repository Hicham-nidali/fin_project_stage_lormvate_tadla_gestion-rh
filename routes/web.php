<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HRAdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\DepartmentHeadAttendanceController;
use App\Http\Controllers\HRAttendanceController;
use App\Http\Controllers\EvaluationReportController;
use App\Http\Controllers\HREvaluationReportController;
use App\Http\Controllers\HRPayrollController;
use App\Http\Controllers\EmployeePayrollController;
use App\Http\Controllers\DirectionController;

// Redirection racine
Route::get('/', function () {
    return redirect('/login');
});

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Route pour maintenir la session active
Route::post('/heartbeat', function () {
    return response()->json(['status' => 'ok']);
})->middleware('auth');

// ===================
// ADMINISTRATION RH
// ===================
Route::middleware(['auth', \App\Http\Middleware\HRAdminMiddleware::class])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/', [HRAdminController::class, 'dashboard'])->name('dashboard');
    
    // Gestion des départements
    Route::resource('departments', HRAdminController::class, [
        'only' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'],
        'names' => [
            'index' => 'departments.index',
            'create' => 'departments.create',
            'store' => 'departments.store',
            'show' => 'departments.show',
            'edit' => 'departments.edit',
            'update' => 'departments.update',
            'destroy' => 'departments.destroy'
        ]
    ]);
    
    // Gestion des utilisateurs
    Route::get('/users', [HRAdminController::class, 'usersIndex'])->name('users.index');
    Route::get('/users/create', [HRAdminController::class, 'usersCreate'])->name('users.create');
    Route::post('/users', [HRAdminController::class, 'usersStore'])->name('users.store');
    Route::get('/users/{id}/edit', [HRAdminController::class, 'usersEdit'])->name('users.edit');
    Route::put('/users/{id}', [HRAdminController::class, 'usersUpdate'])->name('users.update');
    Route::delete('/users/{id}', [HRAdminController::class, 'usersDestroy'])->name('users.destroy');
    
    // Actions d'assignation
    Route::post('/assign-employee', [HRAdminController::class, 'assignEmployee'])->name('assign-employee');
    Route::post('/remove-employee/{userId}', [HRAdminController::class, 'removeEmployee'])->name('remove-employee');
    
    // Pointage RH Admin
    Route::get('/attendance', [HRAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [HRAttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [HRAttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/attendance/history', [HRAttendanceController::class, 'history'])->name('attendance.history');
    
    // RAPPORTS D'ÉVALUATION - ADMINISTRATION RH
    Route::prefix('evaluation-reports')->name('evaluation-reports.')->group(function () {
        Route::get('/', [HREvaluationReportController::class, 'index'])->name('index');
        Route::get('/dashboard', [HREvaluationReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/{id}', [HREvaluationReportController::class, 'show'])->name('show');
        Route::get('/{id}/review', [HREvaluationReportController::class, 'review'])->name('review');
        Route::post('/{id}/review', [HREvaluationReportController::class, 'storeReview'])->name('store-review');
    });

    // GESTION PAIE - HR ADMIN
    Route::get('/payroll', [HRPayrollController::class, 'dashboard'])->name('payroll.dashboard');
    
    // Gestion des salaires
    Route::prefix('payroll/salaries')->name('payroll.salaries.')->group(function () {
        Route::get('/', [HRPayrollController::class, 'salariesIndex'])->name('index');
        Route::get('/create', [HRPayrollController::class, 'salariesCreate'])->name('create');
        Route::post('/store', [HRPayrollController::class, 'salariesStore'])->name('store');
        Route::get('/{id}/edit', [HRPayrollController::class, 'salariesEdit'])->name('edit');
        Route::put('/{id}', [HRPayrollController::class, 'salariesUpdate'])->name('update');
    });
    
    // Bulletins de paie
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/bulletins', [HRPayrollController::class, 'payrollIndex'])->name('index');
        Route::post('/calculate', [HRPayrollController::class, 'calculatePayroll'])->name('calculate');
        Route::get('/{id}', [HRPayrollController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [HRPayrollController::class, 'approve'])->name('approve');
        Route::post('/{id}/paid', [HRPayrollController::class, 'markAsPaid'])->name('mark-paid');
        Route::post('/bulk-approve', [HRPayrollController::class, 'bulkApprove'])->name('bulk-approve');
        Route::get('/export/csv', [HRPayrollController::class, 'export'])->name('export');
        Route::get('/reports/stats', [HRPayrollController::class, 'reports'])->name('reports');
    });
});

// ===================
// CHEF DE DÉPARTEMENT
// ===================
Route::middleware(['auth', \App\Http\Middleware\DepartmentHeadMiddleware::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Gestion d'équipe
    Route::resource('team', TeamController::class)->only(['index', 'show', 'edit', 'update']);
    
    // Présences équipe
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
    
    // Pointage personnel Chef de Département
    Route::prefix('department-head')->name('department-head.')->group(function () {
        Route::get('/attendance', [DepartmentHeadAttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/check-in', [DepartmentHeadAttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('/attendance/check-out', [DepartmentHeadAttendanceController::class, 'checkOut'])->name('attendance.check-out');
        Route::get('/attendance/history', [DepartmentHeadAttendanceController::class, 'history'])->name('attendance.history');
    });
    
    // Tâches
    Route::resource('tasks', TaskController::class);
    Route::get('/tasks/{task}/proof/view', [TaskController::class, 'viewProof'])->name('tasks.proof.view');
    Route::get('/tasks/{task}/proof/download', [TaskController::class, 'downloadProof'])->name('tasks.proof.download');
    Route::post('/tasks/{id}/validate', [TaskController::class, 'validateCompletion'])->name('tasks.validate');
    
    // Évaluations
    Route::resource('evaluations', EvaluationController::class);
    
    // Rapports
    Route::resource('reports', ReportController::class);
    Route::get('/reports/generate/monthly', [ReportController::class, 'generateMonthlyReport'])->name('reports.generate.monthly');
    
    // RAPPORTS D'ÉVALUATION - CHEF DE DÉPARTEMENT
    Route::prefix('evaluation-reports')->name('evaluation-reports.')->group(function () {
        Route::get('/', [EvaluationReportController::class, 'index'])->name('index');
        Route::get('/create', [EvaluationReportController::class, 'create'])->name('create');
        Route::post('/store', [EvaluationReportController::class, 'store'])->name('store');
        Route::get('/{id}', [EvaluationReportController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [EvaluationReportController::class, 'edit'])->name('edit');
        Route::put('/{id}', [EvaluationReportController::class, 'update'])->name('update');
        Route::post('/{id}/send', [EvaluationReportController::class, 'send'])->name('send');
    });
    
    // Demandes
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{id}', [RequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{id}/approve', [RequestController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{id}/reject', [RequestController::class, 'reject'])->name('requests.reject');
    
    // HEURES SUPPLÉMENTAIRES - CHEF DE DÉPARTEMENT
    Route::prefix('overtime')->name('overtime.')->group(function () {
        Route::get('/', [\App\Http\Controllers\OvertimeController::class, 'index'])->name('index');
        Route::get('/report', [\App\Http\Controllers\OvertimeController::class, 'report'])->name('report');
        Route::get('/{id}', [\App\Http\Controllers\OvertimeController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [\App\Http\Controllers\OvertimeController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [\App\Http\Controllers\OvertimeController::class, 'reject'])->name('reject');
    });
    
    // GESTION DES OBJECTIFS - CHEF DE DÉPARTEMENT
    Route::prefix('objectives')->name('objectives.')->group(function () {
        Route::get('/', [App\Http\Controllers\DepartmentHeadObjectiveController::class, 'index'])->name('index');
        Route::get('/dashboard', [App\Http\Controllers\DepartmentHeadObjectiveController::class, 'dashboard'])->name('dashboard');
        Route::get('/{id}', [App\Http\Controllers\DepartmentHeadObjectiveController::class, 'show'])->name('show');
        Route::post('/{id}/update-progress', [App\Http\Controllers\DepartmentHeadObjectiveController::class, 'updateProgress'])->name('update-progress');
        Route::post('/{id}/complete', [App\Http\Controllers\DepartmentHeadObjectiveController::class, 'complete'])->name('complete');
        Route::post('/{id}/reopen', [App\Http\Controllers\DepartmentHeadObjectiveController::class, 'reopen'])->name('reopen');
        Route::get('/history/all', [App\Http\Controllers\DepartmentHeadObjectiveController::class, 'history'])->name('history');
    });
});

// ===================
// EMPLOYÉS
// ===================
Route::middleware(['auth'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/', [\App\Http\Controllers\EmployeeDashboardController::class, 'index'])->name('dashboard');
    
    // Pointage
    Route::get('/attendance', [\App\Http\Controllers\EmployeeAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [\App\Http\Controllers\EmployeeAttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [\App\Http\Controllers\EmployeeAttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/attendance/history', [\App\Http\Controllers\EmployeeAttendanceController::class, 'history'])->name('attendance.history');
    
    // Profil
    Route::get('/profile', [\App\Http\Controllers\EmployeeProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [\App\Http\Controllers\EmployeeProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [\App\Http\Controllers\EmployeeProfileController::class, 'update'])->name('profile.update');
    
    // Tâches
    Route::get('/tasks', [\App\Http\Controllers\EmployeeTaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{id}', [\App\Http\Controllers\EmployeeTaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{id}/status', [\App\Http\Controllers\EmployeeTaskController::class, 'updateStatus'])->name('tasks.status');
    
    // Demandes
    Route::get('/requests', [\App\Http\Controllers\EmployeeRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [\App\Http\Controllers\EmployeeRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests/store', [\App\Http\Controllers\EmployeeRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{id}', [\App\Http\Controllers\EmployeeRequestController::class, 'show'])->name('requests.show');
    
    // Messages
    Route::get('/messages', [\App\Http\Controllers\EmployeeMessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{id}', [\App\Http\Controllers\EmployeeMessageController::class, 'show'])->name('messages.show');
    
    // HEURES SUPPLÉMENTAIRES - EMPLOYÉS
    Route::prefix('overtime')->name('overtime.')->group(function () {
        Route::get('/', [\App\Http\Controllers\EmployeeOvertimeController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\EmployeeOvertimeController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\EmployeeOvertimeController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\EmployeeOvertimeController::class, 'show'])->name('show');
        Route::get('/history', [\App\Http\Controllers\EmployeeOvertimeController::class, 'history'])->name('history');
    });

    // BULLETINS PAIE - EMPLOYÉS
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/', [EmployeePayrollController::class, 'index'])->name('index');
        Route::get('/{id}', [EmployeePayrollController::class, 'show'])->name('show');
        Route::get('/history/all', [EmployeePayrollController::class, 'history'])->name('history');
        Route::get('/{id}/download', [EmployeePayrollController::class, 'download'])->name('download');
        Route::get('/{id}/performance', [EmployeePayrollController::class, 'performanceDetails'])->name('performance');
        Route::get('/compare/periods', [EmployeePayrollController::class, 'compare'])->name('compare');
    });
});

// ===================
// DIRECTION
// ===================
Route::middleware(['auth', \App\Http\Middleware\DirectionMiddleware::class])->prefix('direction')->name('direction.')->group(function () {
    Route::get('/dashboard', [DirectionController::class, 'dashboard'])->name('dashboard');
    Route::get('/attendance', [DirectionController::class, 'attendance'])->name('attendance');
    Route::get('/attendance/report', [DirectionController::class, 'attendanceReport'])->name('attendance.report');
    
    // GESTION DES UTILISATEURS - DIRECTION
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [DirectionController::class, 'usersIndex'])->name('index');
        Route::get('/create', [DirectionController::class, 'usersCreate'])->name('create');
        Route::post('/store', [DirectionController::class, 'usersStore'])->name('store');
        Route::get('/{id}/edit', [DirectionController::class, 'usersEdit'])->name('edit');
        Route::put('/{id}', [DirectionController::class, 'usersUpdate'])->name('update');
        Route::delete('/{id}', [DirectionController::class, 'usersDestroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [DirectionController::class, 'toggleUserStatus'])->name('toggle-status');
        Route::post('/assign-department', [DirectionController::class, 'assignDepartment'])->name('assign-department');
    });
    
    // GESTION DES OBJECTIFS - DIRECTION
    Route::prefix('objectives')->name('objectives.')->group(function () {
        Route::get('/', [App\Http\Controllers\ObjectiveController::class, 'index'])->name('index');
        Route::get('/dashboard', [App\Http\Controllers\ObjectiveController::class, 'dashboard'])->name('dashboard');
        Route::get('/create', [App\Http\Controllers\ObjectiveController::class, 'create'])->name('create');
        Route::post('/store', [App\Http\Controllers\ObjectiveController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\ObjectiveController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\ObjectiveController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\ObjectiveController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\ObjectiveController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/cancel', [App\Http\Controllers\ObjectiveController::class, 'cancel'])->name('cancel');
        Route::get('/reports/global', [App\Http\Controllers\ObjectiveController::class, 'report'])->name('report');
    });
    Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [App\Http\Controllers\DirectionReportController::class, 'index'])->name('index');
    Route::get('/dashboard', [App\Http\Controllers\DirectionReportController::class, 'dashboard'])->name('dashboard');
    Route::get('/department/{id}', [App\Http\Controllers\DirectionReportController::class, 'showDepartmentReport'])->name('show.department');
    Route::get('/evaluation/{id}', [App\Http\Controllers\DirectionReportController::class, 'showEvaluationReport'])->name('show.evaluation');
    Route::get('/export', [App\Http\Controllers\DirectionReportController::class, 'export'])->name('export');
    
    // APIs pour les graphiques
    Route::get('/api/by-status', [App\Http\Controllers\DirectionReportController::class, 'reportsByStatus'])->name('api.by-status');
    Route::get('/api/by-department', [App\Http\Controllers\DirectionReportController::class, 'reportsByDepartment'])->name('api.by-department');
    Route::get('/api/monthly-trends', [App\Http\Controllers\DirectionReportController::class, 'monthlyTrends'])->name('api.monthly-trends');
});
});