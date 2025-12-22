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
        Schema::table('stock_receiving_items', function (Blueprint $table) {
            $table->integer('bonus_quantity')->default(0)->after('quantity');
            $table->text('bonus_notes')->nullable()->after('bonus_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_receiving_items', function (Blueprint $table) {
            $table->dropColumn(['bonus_quantity', 'bonus_notes']);
        });
    }
};
