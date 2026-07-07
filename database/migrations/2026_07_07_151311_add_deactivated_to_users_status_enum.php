<?php
// database/migrations/2026_07_07_000000_add_deactivated_to_users_status_enum.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active', 'suspended', 'frozen', 'deactivated') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active', 'suspended', 'frozen') NOT NULL DEFAULT 'active'");
    }
};