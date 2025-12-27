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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            // Use explicit, shorter lengths so composite indexes fit MySQL's index size limits
            $table->string('zone', 100); // Front Store, Storage Room, Fridge, etc.
            $table->string('cabinet_shelf', 100)->nullable(); // Shelf A, Rack 4, Drawer 1, etc.
            $table->string('row_level', 50)->nullable(); // Row 2, Level 1, etc.
            $table->string('position_side', 50)->nullable(); // Position 3, Left, Right, etc.
            $table->string('full_location'); // Auto-generated full location string
            $table->text('description')->nullable(); // Additional notes about the location
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes for better performance
            $table->index('zone');
            $table->index('is_active');
            // Use a single unique index on the generated full_location string
            // instead of a wide composite index across multiple varchar columns.
            $table->unique('full_location', 'unique_location_full');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
