<?php

namespace App\Models;

use App\Enums\BorrowingStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Borrowing extends Model
{
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
        'status' => 'borrowed',
    ];

    protected $casts = [
        'status'        => BorrowingStatus::class,
        'borrowed_date' => 'date',
        'due_date'      => 'date',
        'returned_date' => 'date',
    ];

    protected $appends = ['is_overdue'];

    // Relationships
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    // Computed
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === BorrowingStatus::Overdue;
    }

    // Checks
    public function isOverdue(): bool
    {
        return $this->status === BorrowingStatus::Overdue;
    }

    public function canBeReturned(): bool
    {
        return $this->status->canBeReturned();
    }

    // Actions
    public function markAsReturned(): void
    {
        $this->update([
            'status'        => BorrowingStatus::Returned,
            'returned_date' => now(),
        ]);
    }

    public static function markAllOverdue(): int
    {
        return self::where('status', BorrowingStatus::Borrowed)
            ->where('due_date', '<', now())
            ->update(['status' => BorrowingStatus::Overdue]);
    }

    // Scopes
    public function scopeOverdue($query)
    {
        return $query->where('status', BorrowingStatus::Overdue);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            BorrowingStatus::Borrowed,
            BorrowingStatus::Overdue,
        ]);
    }
}
