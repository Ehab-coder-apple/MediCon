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
        Schema::table('attendances', function (Blueprint $table) {
            // Break tracking fields
            $table->timestamp('break_start_time')->nullable()->after('check_in_time');
            $table->timestamp('break_end_time')->nullable()->after('break_start_time');
            $table->integer('break_duration_minutes')->nullable()->after('break_end_time')->comment('Total break duration in minutes');
            $table->integer('total_break_count')->default(0)->after('break_duration_minutes')->comment('Number of breaks taken');

            // Index for break queries
            $table->index('break_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['break_start_time']);
            $table->dropColumn(['break_start_time', 'break_end_time', 'break_duration_minutes', 'total_break_count']);
        });
    }
};
