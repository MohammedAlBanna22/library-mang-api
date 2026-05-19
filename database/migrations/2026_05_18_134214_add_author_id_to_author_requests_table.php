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
                // 👈 أضف الـ FK
        $table->foreignId('author_id')
              ->nullable()
              ->constrained()
              ->onDelete('cascade')
              ->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('author_requests', function (Blueprint $table) {
            //
        $table->dropForeign(['author_id']);
        $table->dropColumn('author_id');
        });
    }
};
