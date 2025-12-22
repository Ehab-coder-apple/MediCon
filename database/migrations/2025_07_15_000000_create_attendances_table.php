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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->date('attendance_date');
            
            // Check-in information
            $table->timestamp('check_in_time')->nullable();
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->boolean('check_in_within_geofence')->default(false);
            $table->float('check_in_distance_meters')->nullable();
            $table->text('check_in_notes')->nullable();
            $table->string('check_in_device_info')->nullable();
            
            // Check-out information
            $table->timestamp('check_out_time')->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->boolean('check_out_within_geofence')->default(false);
            $table->float('check_out_distance_meters')->nullable();
            $table->text('check_out_notes')->nullable();
            $table->string('check_out_device_info')->nullable();
            
            // Duration and status
            $table->integer('total_minutes_worked')->nullable();
            $table->enum('status', ['pending', 'checked_in', 'checked_out', 'incomplete'])->default('pending');
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            
            // Indexes
            $table->index(['tenant_id', 'attendance_date']);
            $table->index(['user_id', 'attendance_date']);
            $table->index(['branch_id', 'attendance_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

