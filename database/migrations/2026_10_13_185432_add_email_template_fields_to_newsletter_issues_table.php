<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_issues', function (Blueprint $table): void {
            $table->string('email_template')->nullable();
            $table->text('email_deck')->nullable();
            $table->json('email_options')->nullable();
        });
    }
};
