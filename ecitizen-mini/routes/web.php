<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CitizenController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\OfficerController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect(auth()->user()->home()) : redirect()->route('login'));

// Public: verify a certificate by scanning its QR code
Route::get('/verify/{code}', [CertificateController::class, 'verify'])->name('certificate.verify');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/verify-email', [AuthController::class, 'showVerify'])->name('otp.show');
    Route::post('/verify-email', [AuthController::class, 'verify'])->middleware('throttle:6,1')->name('otp.verify');
    Route::post('/verify-email/resend', [AuthController::class, 'resend'])->middleware('throttle:3,1')->name('otp.resend');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Uploaded documents (authorisation checked inside controller)
Route::get('/files/{file}', [FileController::class, 'show'])
    ->middleware(['auth', RoleMiddleware::class . ':citizen,officer,admin'])->name('files.show');

// ---- Citizen ----
Route::middleware(['auth', RoleMiddleware::class . ':citizen'])->prefix('citizen')->name('citizen.')->group(function () {
    Route::get('/dashboard', [CitizenController::class, 'dashboard'])->name('dashboard');
    Route::get('/services/{service}/apply', [CitizenController::class, 'apply'])->name('apply');
    Route::post('/services/{service}/apply', [CitizenController::class, 'store'])->name('store');
    Route::get('/applications', [CitizenController::class, 'applications'])->name('applications');
    Route::get('/applications/{application}', [CitizenController::class, 'show'])->name('show');
    Route::get('/applications/{application}/edit', [CitizenController::class, 'edit'])->name('edit');
    Route::put('/applications/{application}', [CitizenController::class, 'update'])->name('update');
    Route::post('/applications/{application}/pay', [CitizenController::class, 'pay'])->name('pay');
    Route::get('/applications/{application}/certificate', [CertificateController::class, 'show'])->name('certificate');
});

// ---- Department official ----
Route::middleware(['auth', RoleMiddleware::class . ':officer'])->prefix('officer')->name('officer.')->group(function () {
    Route::get('/queue', [OfficerController::class, 'queue'])->name('queue');
    Route::get('/applications/{application}', [OfficerController::class, 'review'])->name('review');
    Route::post('/applications/{application}/decision', [OfficerController::class, 'decide'])->name('decide');
});

// ---- System admin ----
Route::middleware(['auth', RoleMiddleware::class . ':admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/services', [AdminController::class, 'services'])->name('services');
    Route::get('/services/create', [AdminController::class, 'createService'])->name('services.create');
    Route::post('/services', [AdminController::class, 'storeService'])->name('services.store');
    Route::get('/services/{service}/edit', [AdminController::class, 'editService'])->name('services.edit');
    Route::put('/services/{service}', [AdminController::class, 'updateService'])->name('services.update');
    Route::get('/departments', [AdminController::class, 'departments'])->name('departments');
    Route::post('/departments', [AdminController::class, 'storeDepartment'])->name('departments.store');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
});
