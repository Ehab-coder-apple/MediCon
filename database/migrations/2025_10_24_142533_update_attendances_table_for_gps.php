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
            // Add GPS columns if they don't exist
            if (!Schema::hasColumn('attendances', 'check_in_latitude')) {
                $table->decimal('check_in_latitude', 10, 8)->nullable()->after('check_in_time');
                $table->decimal('check_in_longitude', 11, 8)->nullable()->after('check_in_latitude');
                $table->boolean('check_in_within_geofence')->default(false)->after('check_in_longitude');
                $table->float('check_in_distance_meters')->nullable()->after('check_in_within_geofence');
            }

            if (!Schema::hasColumn('attendances', 'check_out_latitude')) {
                $table->decimal('check_out_latitude', 10, 8)->nullable()->after('check_out_time');
                $table->decimal('check_out_longitude', 11, 8)->nullable()->after('check_out_latitude');
                $table->boolean('check_out_within_geofence')->default(false)->after('check_out_longitude');
                $table->float('check_out_distance_meters')->nullable()->after('check_out_within_geofence');
            }

            if (!Schema::hasColumn('attendances', 'check_in_device_info')) {
                $table->string('check_in_device_info')->nullable()->after('check_out_notes');
                $table->string('check_out_device_info')->nullable()->after('check_in_device_info');
            }

            // Add indexes
            if (!Schema::hasIndex('attendances', 'attendances_tenant_id_attendance_date_index')) {
                $table->index(['tenant_id', 'attendance_date']);
            }
            if (!Schema::hasIndex('attendances', 'attendances_user_id_attendance_date_index')) {
                $table->index(['user_id', 'attendance_date']);
            }
            if (!Schema::hasIndex('attendances', 'attendances_branch_id_attendance_date_index')) {
                $table->index(['branch_id', 'attendance_date']);
            }
            if (!Schema::hasIndex('attendances', 'attendances_status_index')) {
                $table->index('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndexIfExists('attendances_tenant_id_attendance_date_index');
            $table->dropIndexIfExists('attendances_user_id_attendance_date_index');
            $table->dropIndexIfExists('attendances_branch_id_attendance_date_index');
            $table->dropIndexIfExists('attendances_status_index');

            $columns = [
                'check_in_latitude',
                'check_in_longitude',
                'check_in_within_geofence',
                'check_in_distance_meters',
                'check_out_latitude',
                'check_out_longitude',
                'check_out_within_geofence',
                'check_out_distance_meters',
                'check_in_device_info',
                'check_out_device_info',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('attendances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
