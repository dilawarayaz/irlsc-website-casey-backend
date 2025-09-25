<?php


use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\UserImageController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;

// Existing auth routes (assume you have them)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/getall-users', [AdminUserController::class, 'index']);
Route::get('/users/{id}', [AdminUserController::class, 'show']);
// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Route::get('user/profile', [UserProfileController::class, 'show']);
    Route::post('profile/update', [AuthController::class, 'update']);
    Route::post('profile/images', [UserImageController::class, 'store']);
    Route::delete('/images/{id}', [UserImageController::class, 'destroy']);
    Route::get('/user', [AuthController::class, 'show']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::post('/profile', [ProfileController::class, 'store']);
    Route::get('/profile', [ProfileController::class, 'show']);
    // Questions (public fetch)
    Route::get('/questions', [QuestionController::class, 'index']);
    Route::get('/matches', [MatchController::class, 'index']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
    Route::post('/questions', [QuestionController::class, 'store']);
    Route::put('/questions/{question}', [QuestionController::class, 'update']);
    Route::delete('/questions/{question}', [QuestionController::class, 'destroy']);
});
