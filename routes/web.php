<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameAttendeeController;
use App\Http\Controllers\GameImageController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupPlayerController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\NightCommentController;
use App\Http\Controllers\PokerNightController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RsvpController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\SuperAdmin\GroupController as SuperAdminGroupController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', fn () => view('home', ['prefillEmail' => request()->query('email', '')]))->name('home');

Route::get('/login', [LoginController::class, 'show'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'show'])->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'store'])->middleware('guest');
Route::get('/username-check', [RegisterController::class, 'checkUsername'])->name('username.check');

// Invite link (public — works for both guests and logged-in users)
Route::get('/invite/{token}', [InviteController::class, 'show'])->name('invite.show');

// Authenticated
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Groups
    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/join/{code}', [GroupController::class, 'joinForm'])->name('groups.join');
    Route::post('/groups/join/{code}', [GroupController::class, 'join'])->name('groups.join.post');
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');

    // Leaderboard
    Route::get('/groups/{group}/leaderboard', [LeaderboardController::class, 'show'])->name('groups.leaderboard');

    // Group player roster (owner/admin only — enforced in controller)
    Route::get('/groups/{group}/players', [GroupPlayerController::class, 'index'])->name('players.index');
    Route::get('/groups/{group}/players/create', [GroupPlayerController::class, 'create'])->name('players.create');
    Route::post('/groups/{group}/players', [GroupPlayerController::class, 'store'])->name('players.store');
    Route::get('/groups/{group}/players/{player}/edit', [GroupPlayerController::class, 'edit'])->name('players.edit');
    Route::put('/groups/{group}/players/{player}', [GroupPlayerController::class, 'update'])->name('players.update');
    Route::delete('/groups/{group}/players/{player}', [GroupPlayerController::class, 'destroy'])->name('players.destroy');

    // Poker nights
    Route::get('/groups/{group}/nights/create', [PokerNightController::class, 'create'])->name('nights.create');
    Route::post('/groups/{group}/nights', [PokerNightController::class, 'store'])->name('nights.store');
    Route::get('/groups/{group}/nights/{night}', [PokerNightController::class, 'show'])->name('nights.show');
    Route::get('/groups/{group}/nights/{night}/edit', [PokerNightController::class, 'edit'])->name('nights.edit');
    Route::put('/groups/{group}/nights/{night}', [PokerNightController::class, 'update'])->name('nights.update');
    Route::delete('/groups/{group}/nights/{night}', [PokerNightController::class, 'destroy'])->name('nights.destroy');

    // Images
    Route::post('/groups/{group}/nights/{night}/images', [GameImageController::class, 'store'])->name('images.store');
    Route::delete('/images/{image}', [GameImageController::class, 'destroy'])->name('images.destroy');

    // Attendees
    Route::post('/groups/{group}/nights/{night}/attendees', [GameAttendeeController::class, 'store'])->name('attendees.store');

    // RSVP
    Route::post('/groups/{group}/nights/{night}/rsvp', [RsvpController::class, 'update'])->name('rsvp.update');

    // Comments
    Route::post('/groups/{group}/nights/{night}/comments', [NightCommentController::class, 'store'])->name('comments.store');
    Route::delete('/groups/{group}/nights/{night}/comments/{comment}', [NightCommentController::class, 'destroy'])->name('comments.destroy');
});

// Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminUserController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
    Route::post('/users/{user}/role', [AdminUserController::class, 'setRole'])->name('users.role');
});

// Super Admin
Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [SuperAdminGroupController::class, 'dashboard'])->name('dashboard');
    Route::get('/groups', [SuperAdminGroupController::class, 'index'])->name('groups.index');
    Route::post('/groups/{group}/toggle', [SuperAdminGroupController::class, 'toggle'])->name('groups.toggle');
});
