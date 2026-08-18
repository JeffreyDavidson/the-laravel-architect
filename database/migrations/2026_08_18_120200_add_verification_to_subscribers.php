<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable()->after('subscribed_at');
            $table->string('verification_token', 64)->nullable()->after('verified_at');
        });

        DB::table('subscribers')->update(['verified_at' => DB::raw('subscribed_at')]);
    }
};
