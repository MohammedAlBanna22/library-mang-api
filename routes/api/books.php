<?php
// routes/api/books.php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

// الكل يشوف
Route::get('/books',        [BookController::class, 'index']);
Route::get('/books/{book}', [BookController::class, 'show']);

// Admin و Author
Route::middleware('role:admin,author')->group(function () {
    Route::post('/books',         [BookController::class, 'store']);
    Route::put('/books/{book}',   [BookController::class, 'update']);
    Route::patch('/books/{book}', [BookController::class, 'update']);
    Route::get('/my-books',       [BookController::class, 'myBooks']);
});

// Admin فقط
Route::middleware('role:admin')->group(function () {
    Route::delete('/books/{book}', [BookController::class, 'destroy']);
});
