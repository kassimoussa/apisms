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
            $table->string('username')->unique()->nullable()->after('name');
            $table->string('password')->nullable()->after('username');
            $table->timestamp('last_login_at')->nullable()->after('password');
            $table->string('status')->default('active')->after('last_login_at'); // active, inactive, suspended
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['username', 'password', 'last_login_at', 'status']);
        });
    }
};