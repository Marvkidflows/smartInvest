<?php
// LOCATION: database/migrations/2026_06_13_000002_add_hold_and_notes_to_deposits_and_withdrawals.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // deposits: admin_notes already exists, only held_at is new
        Schema::table('deposits', function (Blueprint $table) {
            if (!Schema::hasColumn('deposits', 'held_at')) {
                $table->timestamp('held_at')->nullable()->after('processed_at');
            }
            if (!Schema::hasColumn('deposits', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('status');
            }
        });

        // withdrawals: admin_notes already exists, only held_at is new
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'held_at')) {
                $table->timestamp('held_at')->nullable()->after('processed_at');
            }
            if (!Schema::hasColumn('withdrawals', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('status');
            }
        });

        // Allow 'hold' as a status value on both tables.
        // Using raw SQL since MySQL ENUM modification isn't supported via Blueprint change().
        DB::statement(
            "ALTER TABLE deposits MODIFY status ENUM('pending','approved','rejected','hold') DEFAULT 'pending'"
        );
        DB::statement(
            "ALTER TABLE withdrawals MODIFY status ENUM('pending','approved','rejected','hold') DEFAULT 'pending'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE deposits MODIFY status ENUM('pending','approved','rejected') DEFAULT 'pending'"
        );
        DB::statement(
            "ALTER TABLE withdrawals MODIFY status ENUM('pending','approved','rejected') DEFAULT 'pending'"
        );

        Schema::table('deposits', function (Blueprint $table) {
            if (Schema::hasColumn('deposits', 'held_at')) {
                $table->dropColumn('held_at');
            }
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            if (Schema::hasColumn('withdrawals', 'held_at')) {
                $table->dropColumn('held_at');
            }
        });
    }
};