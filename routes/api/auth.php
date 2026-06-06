<?php
// routes/api/auth.php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public  → api/auth/register & api/auth/login
Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

// Protected  → api/auth/logout, api/auth/forgot-password, etc.
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout',          [AuthController::class, 'logout']);
    Route::post('refresh-token',   [AuthController::class, 'refreshToken']);
    Route::get('user', [AuthController::class, 'user']);
});



Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json([
        'id'    => $request->user()->id,
        'name'  => $request->user()->name,
        'email' => $request->user()->email,
        'role'  => $request->user()->role,
    ]);
});
