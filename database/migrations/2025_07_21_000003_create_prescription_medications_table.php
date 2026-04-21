<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_check_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('medication_name');
            $table->string('dosage')->nullable();
            $table->integer('quantity_prescribed');
            $table->enum('availability_status', ['in_stock', 'out_of_stock', 'low_stock'])->default('out_of_stock');
            $table->integer('available_quantity')->default(0);
            $table->integer('confidence_score')->default(100);
            $table->timestamps();
            
            $table->index(['prescription_check_id']);
            $table->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_medications');
    }
};

