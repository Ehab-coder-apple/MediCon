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
        Schema::table('whatsapp_credentials', function (Blueprint $table) {
            // Add integration type column
            $table->enum('integration_type', ['api', 'business_free'])
                ->default('api')
                ->after('webhook_secret')
                ->comment('Type of WhatsApp integration: api or business_free');

            // Business Free mode columns
            $table->string('business_phone_number', 20)
                ->nullable()
                ->after('integration_type')
                ->comment('Phone number for Business Free mode');

            $table->string('business_account_name', 255)
                ->nullable()
                ->after('business_phone_number')
                ->comment('Business account name for Business Free mode');

            // Status columns for each mode
            $table->enum('api_status', ['active', 'inactive', 'error', 'pending'])
                ->default('inactive')
                ->after('business_account_name')
                ->comment('Status of API integration');

            $table->enum('business_free_status', ['active', 'inactive', 'pending'])
                ->default('inactive')
                ->after('api_status')
                ->comment('Status of Business Free integration');

            // Sync and metadata
            $table->timestamp('last_sync_at')
                ->nullable()
                ->after('business_free_status')
                ->comment('Last time credentials were synced');

            $table->string('sync_method', 50)
                ->default('manual')
                ->after('last_sync_at')
                ->comment('Method of syncing: manual or automatic');

            // Error tracking
            $table->text('api_error_message')
                ->nullable()
                ->after('sync_method')
                ->comment('Last API error message');

            $table->text('business_free_error_message')
                ->nullable()
                ->after('api_error_message')
                ->comment('Last Business Free error message');

            // Add indexes for better query performance
            $table->index('integration_type');
            $table->index('api_status');
            $table->index('business_free_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_credentials', function (Blueprint $table) {
            $table->dropIndex(['integration_type']);
            $table->dropIndex(['api_status']);
            $table->dropIndex(['business_free_status']);

            $table->dropColumn([
                'integration_type',
                'business_phone_number',
                'business_account_name',
                'api_status',
                'business_free_status',
                'last_sync_at',
                'sync_method',
                'api_error_message',
                'business_free_error_message',
            ]);
        });
    }
};

