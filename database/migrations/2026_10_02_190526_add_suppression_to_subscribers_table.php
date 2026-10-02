<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * An address that bounced or was reported as spam is kept, with the reason, so it is never
     * emailed again. Two plain nullable columns are added in place, so SQLite does not rebuild
     * the table. Safe to run again.
     */
    public function up(): void
    {
        if (Schema::hasColumn('subscribers', 'suppressed_at')) {
            return;
        }

        Schema::table('subscribers', function (Blueprint $table) {
            $table->timestamp('suppressed_at')
                ->nullable();
            $table->string('suppression_reason')
                ->nullable();
        });
    }
};
