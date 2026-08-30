<?php

use App\Enums\PublishStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->enum('status', array_column(PublishStatus::cases(), 'value'))
                ->default(PublishStatus::Draft->value)
                ->change();
        });
    }
};
