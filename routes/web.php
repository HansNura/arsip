<?php

use App\Http\Controllers\AdminCertificateController;
use App\Http\Controllers\AdminCertificateTemplateController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrainingController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/verify', [CertificateVerificationController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('verify.index');
Route::post('/verify', [CertificateVerificationController::class, 'check'])
    ->middleware('throttle:20,1')
    ->name('verify.check');

Route::get('/verify/s/{token}', [CertificateVerificationController::class, 'showByToken'])
    ->where('token', '[A-Za-z0-9]{16,64}')
    ->middleware('throttle:60,1')
    ->name('verify.token');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/admin/programs', [TrainingController::class, 'programs'])->name('admin.programs.index');
    Route::get('/admin/programs/create', [TrainingController::class, 'createProgram'])->name('admin.programs.create');
    Route::post('/admin/programs', [TrainingController::class, 'storeProgram'])->name('admin.programs.store');
    Route::get('/admin/programs/{program}/edit', [TrainingController::class, 'editProgram'])->name('admin.programs.edit');
    Route::put('/admin/programs/{program}', [TrainingController::class, 'updateProgram'])->name('admin.programs.update');
    Route::delete('/admin/programs/{program}', [TrainingController::class, 'destroyProgram'])->name('admin.programs.destroy');

    Route::get('/admin/batches', [TrainingController::class, 'batches'])->name('admin.batches.index');
    Route::get('/admin/batches/create', [TrainingController::class, 'createBatch'])->name('admin.batches.create');
    Route::post('/admin/batches', [TrainingController::class, 'storeBatch'])->name('admin.batches.store');
    Route::get('/admin/batches/{batch}', [TrainingController::class, 'showBatch'])->name('admin.batches.show');
    Route::get('/admin/batches/{batch}/edit', [TrainingController::class, 'editBatch'])->name('admin.batches.edit');
    Route::put('/admin/batches/{batch}', [TrainingController::class, 'updateBatch'])->name('admin.batches.update');
    Route::delete('/admin/batches/{batch}', [TrainingController::class, 'destroyBatch'])->name('admin.batches.destroy');
    Route::post('/admin/batches/{batch}/enrollments', [TrainingController::class, 'storeEnrollment'])->name('admin.batches.enrollments.store');
    Route::post('/admin/batches/{batch}/sessions', [TrainingController::class, 'storeSession'])->name('admin.batches.sessions.store');
    Route::put('/admin/batches/{batch}/sessions/{trainingSession}/attendance', [TrainingController::class, 'updateAttendance'])->name('admin.batches.attendance.update');
    Route::put('/admin/batches/{batch}/enrollments/{enrollment}/score', [TrainingController::class, 'updateScore'])->name('admin.batches.scores.update');
    Route::get('/admin/participants', [TrainingController::class, 'participants'])->name('admin.participants.index');
});

Route::middleware(['auth', 'role:admin,staf_arsip'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/certificates', [AdminCertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificate-template', [AdminCertificateTemplateController::class, 'edit'])->name('certificate-templates.edit');
        Route::put('/certificate-template', [AdminCertificateTemplateController::class, 'update'])->name('certificate-templates.update');
        Route::post('/certificates/batch', [AdminCertificateController::class, 'storeBatchRequest'])->name('certificates.store-batch-request');
        Route::post('/enrollments/{enrollment}/certificate', [AdminCertificateController::class, 'store'])->name('certificates.store');
        Route::post('/batches/{batch}/certificates', [AdminCertificateController::class, 'storeBatch'])->name('certificates.store-batch');
        Route::get('/certificates/{certificate}/download', [AdminCertificateController::class, 'download'])->name('certificates.download');
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');
    });
