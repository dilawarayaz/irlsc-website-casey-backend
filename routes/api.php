<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ManualMatchController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\MatchRequestController;
use App\Http\Controllers\UserImageController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\EventController;

// Existing auth routes (assume you have them)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::get('/getall-users', [AdminUserController::class, 'index']);
Route::get('/users/{id}', [AdminUserController::class, 'show']);
Route::get('profiles/public', [ProfileController::class, 'getPublicProfiles']);
Route::get('profiles/private', [ProfileController::class, 'getPrivateProfiles']);

// testing
// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Route::get('user/profile', [UserProfileController::class, 'show']);
     Route::post('/user/profile-picture', [AuthController::class, 'updateProfilePicture']);
    Route::post('/upload-video', [ProfileController::class, 'uploadVideo']);
    Route::post('profile/public/images', [ProfileController::class, 'uploadImages']);
    Route::post('/toggle-profile-type', [ProfileController::class, 'toggleProfileType']);
    Route::get('/my-videos', [ProfileController::class, 'listVideos']);
    Route::post('profile/update', [AuthController::class, 'update']);
    Route::post('profile/images', [UserImageController::class, 'store']);
    Route::delete('profile/images/{id}', [UserImageController::class, 'destroy']);
    Route::get('/user', [AuthController::class, 'show']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::post('/profile', [ProfileController::class, 'store']);
    Route::post('/profile/update-password', [AuthController::class, 'updatePassword']);
    Route::get('/profile', [ProfileController::class, 'show']);
    // Questions (public fetch)
    Route::get('/questions', [QuestionController::class, 'index']);
    Route::get('/matches', [MatchController::class, 'index']);

    // Events routes (available to any authenticated user, including admins)
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);
    Route::post('/events', [EventController::class, 'store']);
    Route::post('/events/{event}/like', [EventController::class, 'toggleLike']);
    Route::post('/events/{event}/attend', [EventController::class, 'toggleAttend']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::put('/questions/{question}', [QuestionController::class, 'update']);
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy']);
    Route::post('/admin/manual-match', [ManualMatchController::class, 'store']);
    Route::get('/admin/{id}/manual-match', [ManualMatchController::class, 'getMatches']);
    Route::post('/user/match-request', [MatchRequestController::class, 'store']);
    Route::post('/admin/match-request/{id}/handle', [MatchRequestController::class, 'handle']);
});
