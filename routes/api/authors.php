<?php
// routes/api/authors.php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\AuthorRequestController;
use Illuminate\Support\Facades\Route;


// الكل يشوف مراجعه
Route::get('/authors',          [AuthorController::class, 'index']);
Route::get('/authors/{author}', [AuthorController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
// 👈 Member
Route::post('/author-requests', [AuthorRequestController::class, 'store']);
Route::get('/author-requests/me', [AuthorRequestController::class, 'myRequest']);

Route::patch('/author/update/me', [AuthorController::class, 'updateMe']);

// Admin فقط
Route::middleware('role:admin')->group(function () {
    Route::post('/authors',           [AuthorController::class, 'store']);
    Route::put('/authors/{author}',   [AuthorController::class, 'update']);
    Route::patch('/authors/{author}', [AuthorController::class, 'update']);
    Route::delete('/authors/{author}',[AuthorController::class, 'destroy']);

    Route::get('/author-requests', [AuthorRequestController::class,'index']);
    Route::patch('/author-requests/{authorRequest}/approve', [AuthorRequestController::class, 'approve']);
    Route::patch('/author-requests/{authorRequest}/reject', [AuthorRequestController::class, 'reject']);

});

});
