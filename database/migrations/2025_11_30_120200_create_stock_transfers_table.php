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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->foreignId('from_warehouse_id');
            $table->foreignId('to_warehouse_id');
            $table->foreignId('user_id')->nullable();
            $table->string('reference')->nullable();
            $table->string('reason')->nullable();
            $table->string('status')->default('completed'); // draft, pending, completed, cancelled
            $table->timestamp('transferred_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'from_warehouse_id']);
            $table->index(['tenant_id', 'to_warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};

