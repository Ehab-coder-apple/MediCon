<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alternative_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_medication_id')->constrained()->onDelete('cascade');
            $table->foreignId('original_medication_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('alternative_product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->integer('similarity_score')->default(0);
            $table->enum('reason', ['same_active_ingredient', 'same_category', 'same_therapeutic_use'])->default('same_category');
            $table->integer('available_quantity')->default(0);
            $table->string('shelf_location')->nullable();
            $table->decimal('price_difference', 10, 2)->nullable();
            $table->timestamps();
            
            $table->index(['prescription_medication_id']);
            $table->index(['branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alternative_products');
    }
};

