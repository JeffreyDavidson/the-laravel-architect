<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN for enum, so we add new columns
        Schema::table('posts', function (Blueprint $table) {
            $table->text('review_notes')->nullable()->after('content');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('review_notes');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');

            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });

        // Change status column: drop and recreate with new enum values
        // SQLite workaround: just update the column comment, the enum is enforced at app level
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['review_notes', 'reviewed_by', 'reviewed_at']);
        });
    }
};
