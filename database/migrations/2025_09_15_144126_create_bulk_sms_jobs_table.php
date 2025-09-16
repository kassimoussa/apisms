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
        Schema::create('bulk_sms_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Campaign name
            $table->text('content'); // SMS message content
            $table->string('from')->nullable(); // Sender ID
            $table->json('recipients'); // Array of phone numbers
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'paused'])->default('pending');
            $table->integer('total_count')->default(0); // Total recipients
            $table->integer('sent_count')->default(0); // Successfully sent
            $table->integer('failed_count')->default(0); // Failed sends
            $table->integer('pending_count')->default(0); // Not yet processed
            $table->timestamp('scheduled_at')->nullable(); // For scheduled sends
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('settings')->nullable(); // Additional settings (rate limiting, etc.)
            $table->text('failure_reason')->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0); // 0-100%
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_sms_jobs');
    }
};
