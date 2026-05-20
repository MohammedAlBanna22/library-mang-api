<?php

namespace App\Console\Commands;

use App\Models\Borrowing;
use Illuminate\Console\Command;

class MarkOverdueBorrowings extends Command
{
    protected $signature   = 'borrowings:mark-overdue';
    protected $description = 'Mark all overdue borrowings';

    public function handle()
    {
        $count = Borrowing::markAllOverdue();
        $this->info("Marked {$count} borrowings as overdue.");
    }
}
