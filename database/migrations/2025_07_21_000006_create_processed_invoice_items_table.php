<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processed_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processed_invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('product_name');
            $table->string('product_code')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 12, 2);
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('manufacturer')->nullable();
            $table->integer('confidence_score')->default(100);
            $table->boolean('manual_correction')->default(false);
            $table->timestamps();
            
            $table->index(['processed_invoice_id']);
            $table->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_invoice_items');
    }
};

