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
        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->foreignId('warehouse_id');
            $table->foreignId('product_id');
            $table->foreignId('batch_id')->nullable();
            $table->integer('quantity');
            $table->timestamps();

            $table->index(['tenant_id', 'warehouse_id']);
            $table->index(['product_id', 'batch_id']);
            $table->unique(['warehouse_id', 'product_id', 'batch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_stocks');
    }
};

