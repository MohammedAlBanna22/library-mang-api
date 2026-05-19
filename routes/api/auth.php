<?php
// routes/api/auth.php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Public
Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);

// Protected (auth:sanctum موجود من الملف الرئيسي)
Route::prefix('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
   // Route::get('user',    [AuthController::class, 'user']);
});

Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
