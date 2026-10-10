<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * branch_requisitions tracks stock *requests* a retail branch sends up
     * to HQ (the branch asking "please send us more of X"), which is the
     * reverse direction of - and a separate workflow from -
     * stock_transfer_orders (HQ pushing stock down to a branch once
     * approved). Approving a requisition here instantiates a matching
     * pending StockTransferOrder from the tenant's Central (HQ Main)
     * warehouse to the requesting branch's Local Backroom warehouse; see
     * BranchRequisitionController::approve().
     */
    public function up(): void
    {
        Schema::create('branch_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            // Nullable: a requisition can either reference an existing
            // catalog product, or (when product_id is null) request a new
            // drug not yet in the system via custom_product_name.
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('custom_product_name')->nullable();
            $table->integer('requested_quantity');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            // Set once an HQ Inventory Manager approves/rejects the
            // requisition, and linked to the stock transfer order it
            // automatically instantiated on approval (nullable - a
            // rejected requisition never gets one).
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('stock_transfer_order_id')->nullable()->constrained('stock_transfer_orders')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_requisitions');
    }
};
