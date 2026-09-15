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
        if (Schema::hasTable('sales') && !Schema::hasColumn('sales', 'shift_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('shift_id')->nullable()->after('user_id')
                    ->constrained('shifts')->nullOnDelete();
                $table->index('shift_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'shift_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropConstrainedForeignId('shift_id');
            });
        }
    }
};
