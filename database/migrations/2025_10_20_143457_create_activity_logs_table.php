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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // e.g., 'created', 'updated', 'deleted', 'viewed'
            $table->string('entity_type'); // e.g., 'Sale', 'Purchase', 'Product', 'User'
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_name')->nullable(); // e.g., product name, customer name
            $table->text('description')->nullable(); // Human-readable description
            $table->json('changes')->nullable(); // Old and new values for updates
            $table->string('category'); // e.g., 'sales', 'purchases', 'inventory', 'users', 'system'
            $table->string('severity')->default('info'); // 'info', 'warning', 'error', 'critical'
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['tenant_id', 'created_at']);
            $table->index(['user_id']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['category']);
            $table->index(['action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
