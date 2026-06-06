<?php
// routes/api/borrowings.php

use App\Http\Controllers\BorrowingController;
use Illuminate\Support\Facades\Route;
Route::middleware('auth:sanctum')->group(function () {
     Route::get('/borrowings',                             [BorrowingController::class, 'index']);

// Admin فقط
Route::middleware('role:admin')->group(function () {

    Route::get('/borrowings/overdue/list',                [BorrowingController::class, 'overdue']);
    Route::get('/books/{book}/borrowing-history', [BorrowingController::class, 'borrowingHistory']);

});

// Admin و Member
Route::middleware('role:admin,member')->group(function () {

    Route::get('/borrowings/{borrowing}', [BorrowingController::class, 'show']);
    Route::post('/borrowings/{borrowing}/return',         [BorrowingController::class, 'returnBook']);
    Route::patch('/borrowings/{borrowing}/renew', [BorrowingController::class, 'renew']);
   // Route::get('members/{member}/borrowings', [BorrowingController::class, 'memberBorrowings']);
   Route::post('borrowings/{borrowing}/fine/pay', [BorrowingController::class, 'payFine']);
});
Route::get('borrowings/{borrowing}/fine', [BorrowingController::class, 'fine']);
  Route::post('/borrowings',            [BorrowingController::class, 'store']);

});
