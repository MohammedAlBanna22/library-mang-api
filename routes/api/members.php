<?php
// routes/api/members.php

use App\Http\Controllers\MemberController;
use App\Http\Resources\MemberResource;
use Illuminate\Support\Facades\Route;


// Admin فقط
// Route::middleware('role:admin')->group(function () {
//     Route::apiResource('members', MemberController::class);
// });



Route::middleware('auth:sanctum')->group(function () {
   // UPDATE (handled inside controller logic)
    Route::patch('/members/me', [MemberController::class, 'updateMe']);

 Route::get('/members/me', function () {
        $member = auth()->user()->member;

        if (!$member) {
            return response()->json([
                'message' => 'Member profile not found. Please create one first.',
                'status' => false
            ], 404);
        }

        return response()->json([
            'data' => new MemberResource($member->load('user'))
        ]);
    });

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