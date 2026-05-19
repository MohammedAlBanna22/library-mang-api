<?php
// routes/api/borrowings.php

use App\Http\Controllers\BorrowingController;
use Illuminate\Support\Facades\Route;

// Admin فقط
Route::middleware('role:admin')->group(function () {
    Route::get('/borrowings',                             [BorrowingController::class, 'index']);
    Route::get('/borrowings/overdue/list',                [BorrowingController::class, 'overdue']);
    Route::post('/borrowings/{borrowing}/return',         [BorrowingController::class, 'returnBook']);
});

// Admin و Member
Route::middleware('role:admin,member')->group(function () {
    Route::post('/borrowings',            [BorrowingController::class, 'store']);
    Route::get('/borrowings/{borrowing}', [BorrowingController::class, 'show']);
});
