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
        Schema::create('pharmacy_product_display_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->enum('display_strategy', [
                'fast_moving',
                'high_stock',
                'nearly_expired',
                'custom_selection'
            ])->default('fast_moving');
            $table->integer('products_limit')->default(20)->comment('Number of products to display');
            $table->timestamps();

            // Foreign key
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            
            // Unique constraint - one setting per tenant
            $table->unique('tenant_id');
            
            // Indexes
            $table->index('display_strategy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_product_display_settings');
    }
};

