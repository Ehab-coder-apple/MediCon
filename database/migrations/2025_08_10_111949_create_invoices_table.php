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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Staff who created invoice
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();

            // Financial details
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);

            // Payment details
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'overdue'])->default('unpaid');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'insurance', 'credit', 'mixed'])->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Invoice status and type
            $table->enum('status', ['draft', 'sent', 'viewed', 'paid', 'cancelled', 'refunded'])->default('draft');
            $table->enum('type', ['sale', 'prescription', 'service', 'consultation'])->default('sale');

            // Additional information
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->string('prescription_id')->nullable(); // Link to prescription if applicable
            $table->json('customer_details')->nullable(); // Store customer info at time of invoice

            // Delivery/pickup information
            $table->enum('delivery_method', ['pickup', 'delivery', 'shipping'])->default('pickup');
            $table->text('delivery_address')->nullable();
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            // Indexes for better performance
            $table->index(['tenant_id', 'invoice_date']);
            $table->index(['customer_id', 'invoice_date']);
            $table->index(['payment_status', 'due_date']);
            $table->index('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
