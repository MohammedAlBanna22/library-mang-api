<?php

use Illuminate\Support\Facades\Route;

// Auth routes (تسجيل/دخول عامة، والباقي محمي داخلياً بملف auth.php نفسه)
Route::prefix('auth')->group(base_path('routes/api/auth.php'));

// باقي الملفات — كل ملف بيدير الحماية الداخلية تبعه بنفسه
require base_path('routes/api/books.php');
require base_path('routes/api/authors.php');
require base_path('routes/api/members.php');
require base_path('routes/api/borrowings.php');
require base_path('routes/api/statistics.php');
