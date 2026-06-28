<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite or MySQL, we need to alter the ENUM constraints to include 'processing' and 'paid'
        if (DB::getDriverName() === 'sqlite') {
            // Re-create table with correct enum is difficult directly in sqlite schema changes via Laravel enum alter,
            // but we can simply change the column definition since SQLite doesn't strict check ENUM unless generated via check constraint.
            // However, the original migration used: $table->enum('status', ['pending', 'paid', 'shipped', 'delivered', 'cancelled']) which creates a CHECK constraint in SQLite.
            // Let's drop the check constraint or recreate the column/table.
            // A simpler way for SQLite in Laravel testing/dev environments is to recreate the column as a simple string, which drops the constraint.
            Schema::table('orders', function (Blueprint $table) {
                $table->string('status')->default('pending')->change();
            });
        } else {
            Schema::table('orders', function (Blueprint $table) {
                $table->enum('status', ['pending', 'processing', 'paid', 'shipped', 'delivered', 'cancelled'])->default('pending')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('status')->default('pending')->change();
            });
        } else {
            Schema::table('orders', function (Blueprint $table) {
                $table->enum('status', ['pending', 'paid', 'shipped', 'delivered', 'cancelled'])->default('pending')->change();
            });
        }
    }
};
