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
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->enum('direction', ['outbound', 'inbound']);
            $table->string('from', 20);
            $table->string('to', 20);
            $table->text('content');
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'expired'])->default('pending');
            $table->string('kannel_id')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('metadata')->nullable(); // Store additional data
            $table->timestamps();
            
            $table->index(['client_id', 'status']);
            $table->index(['direction', 'created_at']);
            $table->index('kannel_id');
            $table->index(['to', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
