<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Borrowing extends Model
{
    /** @use HasFactory<\Database\Factories\BorrowingFactory> */
    use HasFactory;

    protected $fillable = [
        'book_id',
        'member_id',
        'borrowed_date',
        'due_date',
        'returned_date',
        'status',

    ];
    protected $attributes = [
    'status' => 'borrowed', // ✅ always set, even when not passed to create()
    ];

//use cast to use it when read data form table it return as string so when make cast it know model to return data as specifc type that will benefit when use type method lke addday from data type date and retrun 1 that known as ture when cast boolen
    protected $casts = [
        'borrowed_date'  => 'date',
        'due_date'       => 'date',
        'returned_date'  => 'date',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    // Check if borrowing is overdue
    public function isOverdue(): bool
    {
        return $this->due_date < Carbon::today()
            && $this->status === 'borrowed';
    }
}
