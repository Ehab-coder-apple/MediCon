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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->unique()->nullable();
            $table->string('database_name')->nullable();

            // Pharmacy Information
            $table->string('pharmacy_name');
            $table->string('pharmacy_license')->nullable();
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('US');
            $table->string('postal_code')->nullable();

            // Subscription Information
            $table->enum('subscription_plan', ['basic', 'standard', 'premium', 'enterprise'])->default('basic');
            $table->enum('subscription_status', ['active', 'inactive', 'suspended', 'cancelled'])->default('active');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->integer('max_users')->default(5);

            // Status and Settings
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['slug']);
            $table->index(['domain']);
            $table->index(['is_active']);
            $table->index(['subscription_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
