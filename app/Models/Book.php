<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Borrowing;
use App\Models\Author;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'isbn',
        'description',
        'author_id',
        'genre',
        'published_at',
        'total_copies',
        'available_copies',
        'price',
        'cover_image',
        'status',

    ];
    protected $casts = [
    'published_at' => 'date',
];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    public function isAvailable()
    {
        return $this->available_copies > 0;
    }
//decreease avalibale book number
    public function borrow()
    {
        if ($this->available_copies > 0) {
            $this->decrement('available_copies');
        }
    }
// increease avalibale book number
    public function returnBook()
    {
        if ($this->available_copies < $this->total_copies) {
            $this->increment('available_copies');

        }
    }
}
