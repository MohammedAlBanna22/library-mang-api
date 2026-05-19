<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    /** @use HasFactory<\Database\Factories\AuthorFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'nationality',
        'status',
        'phone',
    ];
    public function books(){
        return $this->hasMany(Book::class);
    }
       public function user()
    {
        return $this->belongsTo(User::class);
    }

      public function authorRequest()
    {
        return $this->hasOne(AuthorRequest::class);
    }
}
