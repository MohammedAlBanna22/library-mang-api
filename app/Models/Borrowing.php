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
        'renewal_count',
        'fine_paid',
        'fine_amount',
        'fine_paid_at',

    ];

    protected $attributes = [
        'status' => 'borrowed',
    ];

    protected $casts = [
        'status'        => BorrowingStatus::class,
        'borrowed_date' => 'date',
        'due_date'      => 'date',
        'returned_date' => 'date',
        'fine_paid'     => 'boolean',
        'fine_amount'   => 'decimal:2',
        'fine_paid_at'  => 'datetime',
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
    const FINE_PER_DAY = 0.5;

    public function getOverdueDays(): int
    {
        if (!$this->isOverdue() && $this->status !== BorrowingStatus::Returned) {
            return 0;
        }

        // مرجوع متأخر → من due_date لـ returned_date
        // لسه عنده وoverdue → من due_date لـ now()
        $compareDate = $this->returned_date ?? now();

        $days = $this->due_date->startOfDay()->diffInDays($compareDate->startOfDay());

        return $days > 0 ? (int) $days : 0;
    }

    public function calculateFine(): float
    {
        return round($this->getOverdueDays() * self::FINE_PER_DAY, 2);
    }

    // كم مرة يقدر يجدد
    const MAX_RENEWALS = 2;

   public function canBeRenewed(): bool
    {
        $today = now()->startOfDay();
        $dueDate = $this->due_date->startOfDay();
        $daysUntilDue = $today->diffInDays($dueDate, false);

        // يقدر يجدد من 3 أيام قبل الانتهاء لحد آخر يوم
        $isRenewalWindow = $daysUntilDue >= -0 && $daysUntilDue <= 3;

        return $this->status === BorrowingStatus::Borrowed
            && $this->renewal_count < self::MAX_RENEWALS
            && $isRenewalWindow;
    }


    public function scopeWithFines($query)
    {
        return $query->whereIn('status', [
            BorrowingStatus::Overdue,
            BorrowingStatus::Returned,
        ]);
    }


    public function isFinePaid(): bool
    {
        return $this->fine_paid;
    }

    public function payFine(): void
    {
        $amount = $this->calculateFine(); // احسب قبل ما تحدث
        $this->update([
            'fine_paid'    => true,
            'fine_paid_at' => now(),
             'fine_amount'  => $amount,
        ]);
    }
}