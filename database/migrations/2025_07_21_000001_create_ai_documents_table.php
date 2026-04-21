<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('document_type', ['invoice', 'prescription']);
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size');
            $table->string('mime_type');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('processing_error')->nullable();
            $table->longText('raw_text')->nullable();
            $table->json('extracted_data')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'branch_id']);
            $table->index(['status', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_documents');
    }
};

