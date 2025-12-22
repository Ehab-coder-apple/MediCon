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
        Schema::table('users', function (Blueprint $table) {
            // Drop the old role column
            $table->dropColumn('role');

            // Add new columns
            $table->foreignId('role_id')->nullable()->constrained()->onDelete('set null');
            $table->string('branch_id')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove new columns
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'branch_id', 'is_active']);

            // Restore old role column
            $table->string('role')->default('sales_staff');
        });
    }
};
