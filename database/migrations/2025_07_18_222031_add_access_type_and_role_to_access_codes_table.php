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
        Schema::table('access_codes', function (Blueprint $table) {
            $table->enum('access_type', ['admin_setup', 'user_registration'])->default('user_registration')->after('description');
            $table->enum('role_assignment', ['admin', 'pharmacist', 'sales_staff'])->default('admin')->after('access_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_codes', function (Blueprint $table) {
            $table->dropColumn(['access_type', 'role_assignment']);
        });
    }
};
