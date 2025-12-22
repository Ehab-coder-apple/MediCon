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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();

            // Basic branch information
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();

            // Address information
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('country')->default('USA');
            $table->string('postal_code');

            // GPS coordinates for geofencing
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('geofence_radius')->default(100)->comment('Radius in meters');

            // Contact information
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('manager_name')->nullable();

            // Operating hours (JSON format for flexibility)
            $table->json('operating_hours')->nullable();

            // Status and settings
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_geofencing')->default(true);
            $table->json('settings')->nullable();

            // Multi-tenant support
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['tenant_id', 'is_active']);
            $table->index(['latitude', 'longitude']);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
