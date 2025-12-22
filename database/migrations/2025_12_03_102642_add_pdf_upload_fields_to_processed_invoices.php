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
            // PDF upload fields
            $table->string('pdf_file_path')->nullable()->after('excel_file_path');
            $table->string('pdf_file_name')->nullable()->after('pdf_file_path');
            $table->integer('pdf_file_size')->nullable()->after('pdf_file_name');

            // Warehouse transfer fields
            $table->foreignId('warehouse_id')->nullable()->after('pdf_file_size')->constrained('warehouses')->nullOnDelete();
            $table->foreignId('transfer_approved_by')->nullable()->after('warehouse_id')->constrained('users')->nullOnDelete();
            $table->timestamp('transfer_approved_at')->nullable()->after('transfer_approved_by');
            $table->string('transfer_status')->default('pending')->after('transfer_approved_at'); // pending, approved, completed, failed

            // Item extraction status
            $table->string('extraction_status')->default('pending')->after('transfer_status'); // pending, in_progress, completed, failed
            $table->text('extraction_error')->nullable()->after('extraction_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processed_invoices', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['warehouse_id']);
            $table->dropForeignKeyIfExists(['transfer_approved_by']);
            $table->dropColumn([
                'pdf_file_path',
                'pdf_file_name',
                'pdf_file_size',
                'warehouse_id',
                'transfer_approved_by',
                'transfer_approved_at',
                'transfer_status',
                'extraction_status',
                'extraction_error',
            ]);
        });
    }
};
