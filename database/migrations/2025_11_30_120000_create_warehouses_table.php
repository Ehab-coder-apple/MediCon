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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->foreignId('branch_id')->nullable();
            $table->string('name');
            $table->string('type'); // main, on_shelf, expired, damaged, returns, custom
            $table->boolean('is_sellable')->default(false);
            $table->boolean('is_system')->default(false); // predefined vs custom
            $table->text('specifications')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};

