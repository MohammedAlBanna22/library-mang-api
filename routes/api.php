<?php

// use App\Http\Controllers\AuthController;
// use App\Http\Controllers\AuthorController;
// use App\Http\Controllers\BookController;
// use App\Http\Controllers\BorrowingController;
// use App\Http\Controllers\MemberController;
// use App\Models\Author;
// use App\Models\Book;
// use App\Models\Borrowing;
// use App\Models\Member;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



    // Route::get('/user', function (Request $request) {
    //     return $request->user();
    // })->middleware('auth:sanctum');




    // // ✅ Public
    // Route::prefix('auth')->group(function () {
    // Route::post('register', [AuthController::class, 'register']);
    // Route::post('login',    [AuthController::class, 'login']);
    // });

    // // ✅ Protected
    // Route::middleware('auth:sanctum')->group(function () {

    // // Auth
    //     Route::prefix('auth')->group(function () {
    //         Route::post('logout', [AuthController::class, 'logout']);
    //         Route::get('user',      [AuthController::class, 'user']);
    //     });


    //     Route::apiResource('authors',AuthorController::class);
    //     Route::apiResource('books', BookController::class);
    //     Route::get('/my-books', [BookController::class, 'myBooks']);
    //     Route::apiResource('members', MemberController::class);
    //     Route::apiResource('borrowings', BorrowingController::class)->only(['index', 'store','show']);
    //     Route::post('/borrowings/{borrowing}/return', [BorrowingController::class, 'returnBook']);
    //     Route::get('/borrowings/overdue/list', [BorrowingController::class, 'overdue']);
    //     // statistics
    //     Route::get('/statistics', function () {
    //         return response()->json([
    //         'total_books' => Book::count(),
    //         'total_authors' => Author::count(),
    //         'total_members' => Member::count(),
    //         'books_borrowed' => Borrowing::where('status', 'borrowed')->count(),
    //         'overdue_borrowings' => Borrowing::where('status', 'overdue')->count(),
    //         ]);
    //     });
    // });

// make clean route files and load them here
// Public routes
Route::prefix('auth')->group(base_path('routes/api/auth.php'));

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    require base_path('routes/api/books.php');
    require base_path('routes/api/authors.php');
    require base_path('routes/api/members.php');
    require base_path('routes/api/borrowings.php');
    require base_path('routes/api/statistics.php');
});
