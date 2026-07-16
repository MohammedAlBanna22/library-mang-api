<?php
// routes/api/statistics.php

use App\Models\Author;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use Illuminate\Support\Facades\Route;

// Admin فقط
Route::middleware(['auth:sanctum', 'role:admin'])->get('/statistics', function () {
    return response()->json([
        'total_books'        => Book::count(),
        'total_authors'      => Author::count(),
        'total_members'      => Member::count(),
        'books_borrowed'     => Borrowing::where('status', 'borrowed')->count(),
        'overdue_borrowings' => Borrowing::where('status', 'overdue')->count(),
    ]);
});
