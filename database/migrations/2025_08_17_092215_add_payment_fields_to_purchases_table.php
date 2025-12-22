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
        Schema::table('purchases', function (Blueprint $table) {
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid')->after('status');
            $table->enum('payment_method', ['cash', 'credit', 'bank_transfer', 'check'])->default('credit')->after('payment_status');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('payment_method');
            $table->decimal('balance_due', 12, 2)->default(0)->after('paid_amount');
            $table->date('due_date')->nullable()->after('balance_due');
            $table->date('paid_at')->nullable()->after('due_date');
            $table->text('payment_notes')->nullable()->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'payment_method',
                'paid_amount',
                'balance_due',
                'due_date',
                'paid_at',
                'payment_notes'
            ]);
        });
    }
};
