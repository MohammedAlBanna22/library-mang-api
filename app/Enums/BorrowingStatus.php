<?php

namespace App\Enums;

enum BorrowingStatus: string
{
    //
    case Borrowed = 'borrowed';
    case Overdue  = 'overdue';
    case Returned = 'returned';

    public function canBeReturned(): bool
    {
        return in_array($this, [self::Borrowed, self::Overdue]);
    }
}
