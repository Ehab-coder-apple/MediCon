<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Introduces the HQ / Retail-Pharmacy branch hierarchy required for the
     * corporate chain pharmacy architecture:
     *  - branch_type distinguishes a corporate HQ branch from a retail
     *    storefront branch.
     *  - parent_id lets a RETAIL_PHARMACY branch (including an on-premises
     *    storefront operated at the same physical location as HQ) point back
     *    to its owning HQ branch, while keeping its own sales/stock data
     *    isolated.
     *
     * branch_type defaults to 'RETAIL_PHARMACY' and parent_id defaults to
     * NULL so every existing branch (including the live single-branch
     * tenants already in production) is retroactively upgraded with zero
     * behavioural change: they simply become standalone retail branches
     * with no parent, exactly as they behave today.
     */
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->enum('branch_type', ['HQ', 'RETAIL_PHARMACY'])
                ->default('RETAIL_PHARMACY')
                ->after('tenant_id');

            $table->unsignedBigInteger('parent_id')->nullable()->after('branch_type');

            $table->foreign('parent_id')
                ->references('id')->on('branches')
                ->nullOnDelete();

            $table->index(['tenant_id', 'branch_type']);
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['tenant_id', 'branch_type']);
            $table->dropIndex(['parent_id']);
            $table->dropColumn(['branch_type', 'parent_id']);
        });
    }
};
