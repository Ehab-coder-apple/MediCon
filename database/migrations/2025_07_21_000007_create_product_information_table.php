<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->onDelete('cascade');
            $table->json('active_ingredients')->nullable();
            $table->json('side_effects')->nullable();
            $table->json('indications')->nullable();
            $table->text('dosage_information')->nullable();
            $table->json('contraindications')->nullable();
            $table->json('drug_interactions')->nullable();
            $table->json('storage_requirements')->nullable();
            $table->json('manufacturer_info')->nullable();
            $table->json('regulatory_info')->nullable();
            $table->enum('source', ['manual_entry', 'ai_extracted', 'external_api'])->default('manual_entry');
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_information');
    }
};

