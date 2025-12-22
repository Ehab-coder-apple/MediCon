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
        Schema::create('whats_app_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Sender
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade'); // Recipient (null for bulk messages)
            $table->foreignId('template_id')->nullable()->constrained('whats_app_templates')->onDelete('set null');

            // Message details
            $table->string('recipient_phone');
            $table->string('message_type')->default('text'); // text, template, media
            $table->text('message_content');
            $table->json('template_parameters')->nullable(); // For template messages
            $table->string('media_url')->nullable();
            $table->string('media_type')->nullable(); // image, document, video, audio

            // WhatsApp API details
            $table->string('whatsapp_message_id')->nullable();
            $table->string('status')->default('pending'); // pending, sent, delivered, read, failed
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();

            // Bulk message details
            $table->string('bulk_message_id')->nullable(); // For grouping bulk messages
            $table->boolean('is_bulk_message')->default(false);
            $table->json('bulk_filters')->nullable(); // Filters used for bulk messaging

            // Metadata
            $table->json('metadata')->nullable(); // Additional data
            $table->decimal('cost', 8, 4)->nullable(); // Message cost

            $table->timestamps();

            // Indexes
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['bulk_message_id']);
            $table->index(['sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_messages');
    }
};
