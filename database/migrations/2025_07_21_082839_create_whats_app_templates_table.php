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
        Schema::create('whats_app_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            // Template details
            $table->string('name');
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('category')->default('MARKETING'); // MARKETING, UTILITY, AUTHENTICATION
            $table->string('language')->default('en');

            // Template content
            $table->text('header_text')->nullable();
            $table->text('body_text');
            $table->text('footer_text')->nullable();
            $table->json('buttons')->nullable(); // Call-to-action buttons
            $table->json('parameters')->nullable(); // Template parameters

            // WhatsApp API details
            $table->string('whatsapp_template_id')->nullable();
            $table->string('status')->default('draft'); // draft, pending, approved, rejected
            $table->text('rejection_reason')->nullable();

            // Usage tracking
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();

            // Template settings
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'category']);
            $table->unique(['tenant_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_templates');
    }
};
