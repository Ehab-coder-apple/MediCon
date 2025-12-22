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
        // Users table handled by separate migration

        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('setup_completed')->default(false)->after('is_active');
            $table->string('license_number')->nullable()->after('setup_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Users table handled by separate migration

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['setup_completed', 'license_number']);
        });
    }
};
