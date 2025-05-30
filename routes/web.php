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
    
    // Actions d'assignation
    Route::post('/assign-employee', [HRAdminController::class, 'assignEmployee'])->name('assign-employee');
    Route::post('/remove-employee/{userId}', [HRAdminController::class, 'removeEmployee'])->name('remove-employee');
    
    // Pointage RH Admin
    Route::get('/attendance', [HRAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [HRAttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [HRAttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/attendance/history', [HRAttendanceController::class, 'history'])->name('attendance.history');
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
    
    // Évaluations
    Route::resource('evaluations', EvaluationController::class);
    
    // Rapports
    Route::resource('reports', ReportController::class);
    Route::get('/reports/generate/monthly', [ReportController::class, 'generateMonthlyReport'])->name('reports.generate.monthly');
    
    // Demandes
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{id}', [RequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{id}/approve', [RequestController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{id}/reject', [RequestController::class, 'reject'])->name('requests.reject');
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
});