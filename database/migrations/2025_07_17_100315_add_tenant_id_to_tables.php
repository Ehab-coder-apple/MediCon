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
        // Add tenant_id to users table
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index(['tenant_id']);
            });
        }

        // Add tenant_id to products table
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'tenant_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index(['tenant_id']);
            });
        }

        // Add tenant_id to customers table
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'tenant_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index(['tenant_id']);
            });
        }

        // Add tenant_id to suppliers table
        if (Schema::hasTable('suppliers') && !Schema::hasColumn('suppliers', 'tenant_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index(['tenant_id']);
            });
        }

        // Add tenant_id to sales table
        if (Schema::hasTable('sales') && !Schema::hasColumn('sales', 'tenant_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index(['tenant_id']);
            });
        }

        // Add tenant_id to purchases table
        if (Schema::hasTable('purchases') && !Schema::hasColumn('purchases', 'tenant_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index(['tenant_id']);
            });
        }

        // Add tenant_id to batches table
        if (Schema::hasTable('batches') && !Schema::hasColumn('batches', 'tenant_id')) {
            Schema::table('batches', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index(['tenant_id']);
            });
        }

        // Add tenant_id to roles table
        if (Schema::hasTable('roles') && !Schema::hasColumn('roles', 'tenant_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index(['tenant_id']);
            });
        }

        // Add tenant_id to prescriptions table (if it exists in this installation)
        if (Schema::hasTable('prescriptions') && !Schema::hasColumn('prescriptions', 'tenant_id')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index(['tenant_id']);
            });
        }

        // Add tenant_id to attendances table
        if (Schema::hasTable('attendances') && !Schema::hasColumn('attendances', 'tenant_id')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index(['tenant_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove tenant_id from all tables
        $tables = ['users', 'products', 'customers', 'suppliers', 'sales', 'purchases', 'batches', 'roles', 'prescriptions', 'attendances'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
