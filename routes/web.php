<?php

use App\Http\Controllers\Admin\LearnerAttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LearnerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/dashboard', function () {
    /** @var User $user */
    $user = auth()->user();

    return match (true) {
        $user->hasRole(User::ROLE_ADMIN) => redirect()->route('admin.dashboard'),
        $user->hasRole(User::ROLE_EMPLOYEE) => redirect()->route('employee.dashboard'),
        $user->hasRole(User::ROLE_LEARNER) => redirect()->route('learner.dashboard'),
        default => abort(403, 'No application role is assigned to this account.'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('/register-user', [RegisterController::class, 'showAdminRegisterForm'])->name('register.form');
        Route::post('/register-user', [RegisterController::class, 'registerByAdmin'])
            ->middleware('throttle:5,1')
            ->name('register.user');
        Route::get('/verify-otp', [RegisterController::class, 'showOtpForm'])->name('otp.verify.form');
        Route::post('/verify-otp', [RegisterController::class, 'verifyOtp'])
            ->middleware('throttle:5,1')
            ->name('otp.verify.submit');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/send-welcome-email', [UserController::class, 'sendMail'])->name('users.send-welcome-email');

        Route::get('/email-logs', [EmailLogController::class, 'index'])->name('email-logs.index');
        Route::get('/custom-email', [UserController::class, 'customEmailForm'])->name('custom-email.form');
        Route::post('/custom-email', [UserController::class, 'sendCustomEmail'])->name('custom-email.send');

        Route::resource('learners', LearnerController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::get('/attendance', [LearnerAttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [LearnerAttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/attendance/lookup-learner', [LearnerAttendanceController::class, 'lookupLearner'])
            ->name('attendance.lookup-learner');

        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/send', [AnnouncementController::class, 'sendForm'])->name('announcements.send-form');
        Route::post('/announcements/send', [AnnouncementController::class, 'processSend'])->name('announcements.process-send');
        Route::post('/announcements/{announcement}/resend', [AnnouncementController::class, 'resend'])
            ->name('announcements.resend');
        Route::get('/announcement-logs', [AnnouncementController::class, 'logs'])->name('announcements.logs');
    });

Route::get('/employee/dashboard', [EmployeeController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:employee'])
    ->name('employee.dashboard');

Route::get('/learner/dashboard', [LearnerController::class, 'dashboard'])
    ->middleware(['auth', 'verified', 'role:learner'])
    ->name('learner.dashboard');

require __DIR__.'/auth.php';
