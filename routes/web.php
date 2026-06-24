<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookingRequestController;
use App\Http\Controllers\Auth\LoginController;

// Auth routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
// Đăng ký
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [RoomController::class, 'index'])->name('dashboard')->middleware('auth');

// ...existing code...

Route::middleware(['auth'])->group(function () {
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::get('/rooms/{id}', [RoomController::class, 'show'])->name('rooms.show');

    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::put('/schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
    Route::get('/schedules/{id}', [ScheduleController::class, 'show'])->name('schedules.show');

    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
    Route::get('/courses/{id}', [CourseController::class, 'show'])->name('courses.show');

    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');

    Route::post('/booking-requests', [BookingRequestController::class, 'store'])->name('requests.store');
    Route::put('/booking-requests/{bookingRequest}', [BookingRequestController::class, 'update'])->name('requests.update');
    Route::post('/booking-requests/{bookingRequest}/approve', [BookingRequestController::class, 'approve'])->name('requests.approve');
    Route::post('/booking-requests/{bookingRequest}/reject', [BookingRequestController::class, 'reject'])->name('requests.reject');
    Route::delete('/booking-requests/{bookingRequest}', [BookingRequestController::class, 'destroy'])->name('requests.destroy');
    Route::get('/booking-requests/{id}', [BookingRequestController::class, 'show'])->name('requests.show');
});
