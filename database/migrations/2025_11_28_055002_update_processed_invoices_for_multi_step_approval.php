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
        Schema::table('processed_invoices', function (Blueprint $table) {
            // Add new workflow stage columns
            $table->string('workflow_stage')->default('uploaded')->after('status');
            // 'uploaded', 'approved_for_processing', 'processing', 'processed', 'approved_for_inventory', 'completed'

            // Track approvals
            $table->unsignedBigInteger('approved_for_processing_by')->nullable()->after('reviewed_by');
            $table->dateTime('approved_for_processing_at')->nullable()->after('reviewed_at');

            $table->unsignedBigInteger('approved_for_inventory_by')->nullable()->after('approved_for_processing_by');
            $table->dateTime('approved_for_inventory_at')->nullable()->after('approved_for_processing_at');

            // Track processing
            $table->dateTime('processing_started_at')->nullable()->after('approved_for_inventory_at');
            $table->dateTime('processing_completed_at')->nullable()->after('processing_started_at');

            // Track inventory upload
            $table->dateTime('inventory_uploaded_at')->nullable()->after('processing_completed_at');
            $table->integer('items_added_to_inventory')->default(0)->after('inventory_uploaded_at');

            // Add foreign keys for new approval columns
            $table->foreign('approved_for_processing_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_for_inventory_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processed_invoices', function (Blueprint $table) {
            $table->dropForeign(['approved_for_processing_by']);
            $table->dropForeign(['approved_for_inventory_by']);

            $table->dropColumn([
                'workflow_stage',
                'approved_for_processing_by',
                'approved_for_processing_at',
                'approved_for_inventory_by',
                'approved_for_inventory_at',
                'processing_started_at',
                'processing_completed_at',
                'inventory_uploaded_at',
                'items_added_to_inventory',
            ]);
        });
    }
};
