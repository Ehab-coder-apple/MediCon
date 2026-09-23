<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * stock_transfer_orders is distinct from the existing stock_transfers
     * table: stock_transfers handles same-branch, immediate warehouse-to-
     * warehouse moves (e.g. Main -> On Shelf), while stock_transfer_orders
     * tracks the multi-step, cross-branch dispatch/receipt workflow used to
     * push stock from a Central (HQ) warehouse down to a branch's Local
     * (backroom/shelf) warehouse.
     */
    public function up(): void
    {
        Schema::create('stock_transfer_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_warehouse_id')->constrained('warehouses');
            $table->foreignId('destination_warehouse_id')->constrained('warehouses');
            $table->enum('status', ['pending', 'in_transit', 'received', 'cancelled'])->default('pending');
            $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index('source_warehouse_id');
            $table->index('destination_warehouse_id');
        });

        Schema::create('stock_transfer_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_order_id')->constrained('stock_transfer_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('batch_id')->nullable()->constrained('batches');
            $table->integer('quantity');
            // Unit cost captured at time of transfer, isolated from the
            // live product/batch cost_price so historical transfer value is
            // preserved for future payroll auditing (performance bonuses /
            // waste deductions).
            $table->decimal('currency_unit_cost', 10, 2)->default(0);
            $table->timestamps();

            $table->index('stock_transfer_order_id');
            $table->index(['product_id', 'batch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_order_items');
        Schema::dropIfExists('stock_transfer_orders');
    }
};
