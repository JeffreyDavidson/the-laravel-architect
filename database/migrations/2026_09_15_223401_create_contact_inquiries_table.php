<?php

use App\Enums\ContactInquiryStatus;
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
        Schema::create('contact_inquiries', function (Blueprint $table): void {
            $table->id();
            $table->text('name');
            $table->text('email');
            $table->string('type', 32);
            $table->text('budget')->nullable();
            $table->text('message');
            $table->text('project_title')->nullable();
            $table->enum('status', array_column(ContactInquiryStatus::cases(), 'value'))
                ->default(ContactInquiryStatus::New->value);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_inquiries');
    }
};
