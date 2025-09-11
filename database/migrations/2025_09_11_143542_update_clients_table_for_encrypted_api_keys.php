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
        Schema::table('clients', function (Blueprint $table) {
            // Add new encrypted API key columns
            $table->string('api_key_hash')->nullable()->after('api_key');
            $table->text('api_key_encrypted')->nullable()->after('api_key_hash');
            $table->timestamp('api_key_expires_at')->nullable()->after('api_key_encrypted');
            
            // Add indexes for performance
            $table->index('api_key_hash');
            $table->index('api_key_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['api_key_hash']);
            $table->dropIndex(['api_key_expires_at']);
            $table->dropColumn(['api_key_hash', 'api_key_encrypted', 'api_key_expires_at']);
        });
    }
};
