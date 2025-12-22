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
            $table->string('zone'); // Front Store, Storage Room, Fridge, etc.
            $table->string('cabinet_shelf')->nullable(); // Shelf A, Rack 4, Drawer 1, etc.
            $table->string('row_level')->nullable(); // Row 2, Level 1, etc.
            $table->string('position_side')->nullable(); // Position 3, Left, Right, etc.
            $table->string('full_location'); // Auto-generated full location string
            $table->text('description')->nullable(); // Additional notes about the location
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes for better performance
            $table->index('zone');
            $table->index('is_active');
            $table->index(['zone', 'cabinet_shelf', 'row_level', 'position_side']);
            $table->unique(['zone', 'cabinet_shelf', 'row_level', 'position_side'], 'unique_location');
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
