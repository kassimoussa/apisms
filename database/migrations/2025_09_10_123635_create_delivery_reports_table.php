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
        Schema::create('delivery_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_message_id')->constrained()->onDelete('cascade');
            $table->string('kannel_id');
            $table->enum('status', ['delivered', 'failed', 'buffered', 'smsc_reject', 'smsc_unknown']);
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('delivered_at');
            $table->json('raw_data')->nullable(); // Store raw DLR data from Kannel
            $table->timestamps();
            
            $table->index(['sms_message_id', 'status']);
            $table->index('kannel_id');
            $table->index('delivered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_reports');
    }
};
