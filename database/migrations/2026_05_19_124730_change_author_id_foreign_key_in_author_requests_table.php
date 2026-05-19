<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('author_requests', function (Blueprint $table) {
            //
              // 👈 Step 1: احذف الـ foreign key القديم
            $table->dropForeign(['author_id']);

            // 👈 Step 2: غيّر الـ column لـ nullable
            $table->foreignId('author_id')
                  ->nullable()
                  ->change();

            // 👈 Step 3: أضف الـ foreign key من جديد بـ nullOnDelete
            $table->foreign('author_id')
                  ->references('id')
                  ->on('authors')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('author_requests', function (Blueprint $table) {
            //
        });
    }
};
