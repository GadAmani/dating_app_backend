<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\LikeController;

// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('logout', [AuthController::class, 'logout']);

    // Fetch users of opposite gender
    Route::get('users/opposite-gender', [UserController::class, 'oppositeGender']);

    // Fetch matched profiles
    Route::get('users/matched-profiles', [UserController::class, 'matchedProfiles']);

    Route::middleware('auth:sanctum')->get('/users/liked-me', [UserController::class, 'likedMe']);

    Route::get('/users/liked-me', [UserController::class, 'likedMe']);
    Route::get('/matched-profiles', [UserController::class, 'matchedProfiles']);
    Route::post('/like/{user}', [UserController::class, 'likeUser']);
    Route::post('/dislike/{user}', [UserController::class, 'dislikeUser']);
    Route::get('/matches', [UserController::class, 'getMatches']);
    Route::get('/messages', [UserController::class, 'getMessages']);

});
