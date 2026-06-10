<?php

namespace App\Models;

use App\Enums\MemberStatus;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'membership_date',
        'status',
        'phone',
    ];

    protected $casts = [
        'membership_date' => 'date',
        'status' => MemberStatus::class, // for enum casting to return enum instance instead of string
    ];

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function activeBorrowings(): HasMany
    {
        return $this->borrowings()
            ->where('status', 'borrowed');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isSuspended(): bool
    {
        return $this->status === MemberStatus::Inactive;
    }

    public function suspend(): void
    {
        $this->update(['status' => MemberStatus::Inactive]);
    }

    public function unsuspend(): void
    {
        $this->update(['status' => MemberStatus::Active]);
    }
}
