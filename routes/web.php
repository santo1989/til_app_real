<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // return view('welcome');
    //login redirect
    return redirect()->route('login');
});
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

require __DIR__ . '/auth.php';

Route::middleware(['auth'])->group(function () {
    // Profile routes - available to all authenticated users
    Route::get('/profile', [App\Http\Controllers\UserController::class, 'profile'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\UserController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\UserController::class, 'updateProfile'])->name('profile.update');

    Route::prefix('appraisal')->group(function () {
        // Employee routes
        Route::middleware('role:employee')->group(function () {
            Route::get('/my-objectives', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'myObjectives'])->name('objectives.my');
            Route::post('/submit-objective-setting', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'submit'])->name('objectives.submit');
            Route::get('/midterm-review', [App\Http\Controllers\Appraisal\AppraisalController::class, 'midtermIndex'])->name('appraisals.midterm');
            Route::post('/midterm-review', [App\Http\Controllers\Appraisal\AppraisalController::class, 'midtermSubmit'])->name('appraisals.midterm.submit');
            Route::get('/year-end-self-assessment', [App\Http\Controllers\Appraisal\AppraisalController::class, 'yearEndIndex'])->name('appraisals.yearend');
            Route::post('/year-end-self-assessment', [App\Http\Controllers\Appraisal\AppraisalController::class, 'yearEndSubmit'])->name('appraisals.yearend.submit');
            Route::resource('idp', App\Http\Controllers\Appraisal\IdpController::class)->only(['index', 'edit', 'store', 'update']);
        });

        // Line Manager routes
        Route::middleware('role:line_manager')->group(function () {
            Route::get('/team-objectives', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'teamObjectives'])->name('objectives.team');
            Route::get('/set-objectives/{user_id}', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'showSetForUser'])->name('objectives.show_set_for_user');
            Route::post('/set-objectives/{user_id}', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'setForUser'])->name('objectives.set_for_user');
            Route::get('/conduct-midterm/{user_id}', [App\Http\Controllers\Appraisal\AppraisalController::class, 'conductMidterm'])->name('appraisals.conduct_midterm');
            Route::post('/conduct-midterm/{user_id}', [App\Http\Controllers\Appraisal\AppraisalController::class, 'conductMidtermSubmit'])->name('appraisals.conduct_midterm.submit');
            Route::post('/conduct-midterm-revisions/{user_id}', [App\Http\Controllers\Appraisal\AppraisalController::class, 'conductMidtermRevision'])->name('appraisals.conduct_midterm.revision')->middleware('block.after.9th');
            Route::get('/conduct-year-end/{user_id}', [App\Http\Controllers\Appraisal\AppraisalController::class, 'conductYearEnd'])->name('appraisals.conduct_yearend');
            Route::post('/conduct-year-end/{user_id}', [App\Http\Controllers\Appraisal\AppraisalController::class, 'conductYearEndSubmit'])->name('appraisals.conduct_yearend.submit');
            Route::post('/revise-idp/{user_id}', [App\Http\Controllers\Appraisal\IdpController::class, 'revise'])->name('idp.revise');
            // Year End Assessment (Editable for managers)
            Route::get('/yearend/assessment/{user_id}', [App\Http\Controllers\Appraisal\AppraisalController::class, 'yearendAssessment'])->name('appraisal.yearend.assessment');
            Route::post('/yearend/assessment/{user_id}', [App\Http\Controllers\Appraisal\AppraisalController::class, 'saveYearendAssessment'])->name('appraisal.yearend.assessment.save');

            // Team Objectives CRUD (department-wide, type='team')
            Route::get('/team-objectives-manage', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'teamObjectivesIndex'])->name('team.objectives.index');
            Route::get('/team-objectives-manage/create', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'teamObjectivesCreate'])->name('team.objectives.create');
            Route::post('/team-objectives-manage', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'teamObjectivesStore'])->name('team.objectives.store')->middleware('block.after.9th');
            Route::get('/team-objectives-manage/{team_objective}', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'teamObjectivesShow'])->name('team.objectives.show');
            Route::get('/team-objectives-manage/{team_objective}/edit', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'teamObjectivesEdit'])->name('team.objectives.edit');
            Route::put('/team-objectives-manage/{team_objective}', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'teamObjectivesUpdate'])->name('team.objectives.update')->middleware('block.after.9th');
            Route::delete('/team-objectives-manage/{team_objective}', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'teamObjectivesDestroy'])->name('team.objectives.destroy')->middleware('block.after.9th');
        });

        // Department Head
        Route::middleware('role:dept_head')->group(function () {
            Route::get('/department-objectives', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'departmentObjectives'])->name('objectives.department');
            // Departmental utilities: export CSV, bulk update, inline create (dept head)
            Route::get('/department-objectives/export', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'departmentExport'])->name('department.objectives.export');
            Route::post('/department-objectives/bulk-update', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'departmentBulkUpdate'])->name('department.objectives.bulk_update');
            Route::post('/department-objectives/create', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'departmentCreateInline'])->name('department.objectives.create_inline');
            Route::post('/approve-appraisal/{appraisal_id}', [App\Http\Controllers\Appraisal\AppraisalController::class, 'approve'])->name('appraisals.approve');
        });

        // Board
        Route::middleware('role:board')->group(function () {
            Route::get('/set-departmental-objectives', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'boardIndex'])->name('objectives.board.index');
            Route::post('/set-departmental-objectives', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'boardSet'])->name('objectives.board.set');
        });
        // Team Objectives per-user CRUD (all relevant roles)
        Route::get('/users/{user_id}/objectives', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'userObjectives'])->name('users.objectives.index');
        Route::get('/users/{user_id}/objectives/create', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'createForUser'])->name('users.objectives.create');
        Route::post('/users/{user_id}/objectives', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'storeForUser'])->name('users.objectives.store')->middleware('block.after.9th');
        Route::get('/users/{user_id}/objectives/{objective}/edit', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'editForUser'])->name('users.objectives.edit');
        Route::put('/users/{user_id}/objectives/{objective}', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'updateForUser'])->name('users.objectives.update')->middleware('block.after.9th');
        Route::delete('/users/{user_id}/objectives/{objective}', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'destroyForUser'])->name('users.objectives.destroy')->middleware('block.after.9th');

        // PDF Generation Routes (accessible to employee, line manager, HR, super admin)
        Route::get('/users/{user_id}/objectives/pdf', [App\Http\Controllers\Appraisal\ObjectiveController::class, 'generatePDF'])->name('users.objectives.pdf');
        Route::get('/appraisals/{appraisal_id}/midterm-pdf', [App\Http\Controllers\Appraisal\AppraisalController::class, 'generateMidtermPDF'])->name('appraisals.midterm.pdf');
        Route::get('/appraisals/{appraisal_id}/yearend-pdf', [App\Http\Controllers\Appraisal\AppraisalController::class, 'generateYearEndPDF'])->name('appraisals.yearend.pdf');
        Route::post('/appraisals/{appraisal_id}/sign', [App\Http\Controllers\Appraisal\AppraisalController::class, 'saveSignature'])->name('appraisals.sign');

        // HR Admin - keep appraisal-scoped actions here (reports/override)
        Route::middleware('role:hr_admin')->group(function () {
            Route::get('/reports', [App\Http\Controllers\Appraisal\AppraisalController::class, 'reports'])->name('reports.index');
            Route::post('/override-form/{appraisal_id}', [App\Http\Controllers\Appraisal\AppraisalController::class, 'override'])->name('appraisals.override');
        });

        // PIP management for HR / Super admin
        Route::middleware('role:hr_admin,super_admin')->group(function () {
            Route::get('/pips', [App\Http\Controllers\PipController::class, 'index'])->name('pips.index');
            Route::get('/pips/create', [App\Http\Controllers\PipController::class, 'create'])->name('pips.create');
            Route::post('/pips', [App\Http\Controllers\PipController::class, 'store'])->name('pips.store');
            Route::get('/pips/{pip}', [App\Http\Controllers\PipController::class, 'show'])->name('pips.show');
            Route::get('/pips/{pip}/edit', [App\Http\Controllers\PipController::class, 'edit'])->name('pips.edit');
            Route::put('/pips/{pip}', [App\Http\Controllers\PipController::class, 'update'])->name('pips.update');
            Route::post('/pips/{pip}/close', [App\Http\Controllers\PipController::class, 'close'])->name('pips.close');
            Route::get('/pips-export', [App\Http\Controllers\PipController::class, 'export'])->name('pips.export');
        });
    });


    // HR Admin global resource routes (not under /appraisal) so URIs are /users and /departments
    Route::middleware(['role:hr_admin'])->group(function () {
        Route::resource('users', App\Http\Controllers\UserController::class);
        Route::resource('departments', App\Http\Controllers\DepartmentController::class);
    });

    // Super Admin only: show user table with disguised password column
    Route::middleware(['role:super_admin'])->group(function () {
        Route::get('/super-admin/users', [App\Http\Controllers\UserController::class, 'superAdminUserIndex'])->name('superadmin.users.index');
    });

    // Super Admin & HR Admin full CRUD routes for objectives, appraisals, idps, audit logs
    Route::middleware(['role:hr_admin,super_admin'])->group(function () {
        Route::resource('objectives', App\Http\Controllers\Appraisal\ObjectiveController::class);
        Route::resource('appraisals', App\Http\Controllers\Appraisal\AppraisalController::class);
        Route::resource('idps', App\Http\Controllers\Appraisal\IdpController::class);
        // IDP Milestones
        Route::post('idps/{idp}/milestones', [App\Http\Controllers\Appraisal\IdpMilestoneController::class, 'store'])->name('idps.milestones.store');
        Route::put('idps/{idp}/milestones/{milestone}', [App\Http\Controllers\Appraisal\IdpMilestoneController::class, 'update'])->name('idps.milestones.update');
        Route::delete('idps/{idp}/milestones/{milestone}', [App\Http\Controllers\Appraisal\IdpMilestoneController::class, 'destroy'])->name('idps.milestones.destroy');
        Route::resource('audit-logs', App\Http\Controllers\AuditLogController::class)->names([
            'index' => 'audit-logs.index',
            'create' => 'audit-logs.create',
            'store' => 'audit-logs.store',
            'show' => 'audit-logs.show',
            'edit' => 'audit-logs.edit',
            'update' => 'audit-logs.update',
            'destroy' => 'audit-logs.destroy',
        ]);

        // Financial Years Management
        Route::resource('financial-years', App\Http\Controllers\FinancialYearController::class);
        Route::put('financial-years/{financialYear}/activate', [App\Http\Controllers\FinancialYearController::class, 'activate'])->name('financial-years.activate');
        Route::put('financial-years/{financialYear}/close', [App\Http\Controllers\FinancialYearController::class, 'close'])->name('financial-years.close');
    });

    // Impersonation routes (start only allowed to super_admin)
    // Ensure the {user} parameter is numeric so literal paths like /impersonate/stop
    // do not get captured by the dynamic route and treated as a user id.
    Route::post('/impersonate/{user}', [App\Http\Controllers\ImpersonationController::class, 'start'])
        ->whereNumber('user')
        ->middleware(['auth', 'role:super_admin'])->name('impersonate.start');
    Route::post('/impersonate/stop', [App\Http\Controllers\ImpersonationController::class, 'stop'])
        ->middleware('auth')->name('impersonate.stop');
});
