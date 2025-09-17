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
            // Contact information
            $table->string('email')->nullable()->after('username');
            $table->string('phone')->nullable()->after('email');
            $table->string('company')->nullable()->after('phone');
            $table->text('address')->nullable()->after('company');
            
            // Billing information
            $table->decimal('balance', 10, 2)->default(0)->after('address');
            $table->decimal('credit_limit', 10, 2)->default(0)->after('balance');
            $table->string('currency', 3)->default('EUR')->after('credit_limit');
            
            // Quotas and limits
            $table->integer('daily_sms_limit')->default(1000)->after('rate_limit');
            $table->integer('monthly_sms_limit')->default(30000)->after('daily_sms_limit');
            
            // Business information
            $table->enum('client_type', ['individual', 'business', 'enterprise'])->default('individual')->after('monthly_sms_limit');
            $table->string('industry')->nullable()->after('client_type');
            $table->string('website')->nullable()->after('industry');
            
            // Settings
            $table->json('notification_settings')->nullable()->after('website');
            $table->json('webhook_settings')->nullable()->after('notification_settings');
            $table->boolean('auto_recharge')->default(false)->after('webhook_settings');
            $table->decimal('auto_recharge_amount', 10, 2)->nullable()->after('auto_recharge');
            $table->decimal('auto_recharge_threshold', 10, 2)->nullable()->after('auto_recharge_amount');
            
            // Timestamps
            $table->timestamp('trial_ends_at')->nullable()->after('auto_recharge_threshold');
            $table->timestamp('suspended_at')->nullable()->after('trial_ends_at');
            $table->text('suspension_reason')->nullable()->after('suspended_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'email', 'phone', 'company', 'address',
                'balance', 'credit_limit', 'currency',
                'daily_sms_limit', 'monthly_sms_limit',
                'client_type', 'industry', 'website',
                'notification_settings', 'webhook_settings',
                'auto_recharge', 'auto_recharge_amount', 'auto_recharge_threshold',
                'trial_ends_at', 'suspended_at', 'suspension_reason'
            ]);
        });
    }
};
