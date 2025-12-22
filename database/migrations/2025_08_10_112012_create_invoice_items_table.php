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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');

            // Product details at time of sale (for historical accuracy)
            $table->string('product_name');
            $table->string('product_code');
            $table->text('product_description')->nullable();

            // Quantity and pricing
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 12, 2);

            // Discounts
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);

            // Tax information
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);

            // Batch/expiry information
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();

            // Prescription information (if applicable)
            $table->text('dosage_instructions')->nullable();
            $table->integer('days_supply')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['invoice_id', 'product_id']);
            $table->index('batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
