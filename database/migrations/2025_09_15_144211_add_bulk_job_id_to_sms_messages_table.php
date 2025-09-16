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
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->foreignId('bulk_job_id')->nullable()->after('client_id')->constrained('bulk_sms_jobs')->onDelete('set null');
            $table->index('bulk_job_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropForeign(['bulk_job_id']);
            $table->dropColumn('bulk_job_id');
        });
    }
};
