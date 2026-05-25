<?php
// routes/api/members.php

use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

// Admin فقط
// Route::middleware('role:admin')->group(function () {
//     Route::apiResource('members', MemberController::class);
// });



Route::middleware('auth:sanctum')->group(function () {
   // UPDATE (handled inside controller logic)
    Route::patch('/members/me', [MemberController::class, 'updateMe']);
    // ADMIN ONLY
    Route::middleware('role:admin')->group(function () {
        Route::get('/members', [MemberController::class, 'index']);
        Route::delete('/members/{id}', [MemberController::class, 'destroy']);
        Route::patch('/members/{member}', [MemberController::class, 'update']);
       Route::patch('members/{member}/suspend', [MemberController::class, 'suspend']);

    });

    // ADMIN + MEMBER (create)
    Route::post('/members', [MemberController::class, 'store']);

    // SHOW SELF OR ADMIN
    Route::get('/members/{id}', [MemberController::class, 'show']);


    Route::get('members/{member}/fines', [MemberController::class, 'fines']);
    Route::get('members/{member}/borrowings', [MemberController::class, 'borrowings']);



});
