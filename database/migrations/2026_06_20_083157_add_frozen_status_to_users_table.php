<?php
// LOCATION: database/migrations/2026_06_19_000006_add_frozen_status_to_users.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE users MODIFY status ENUM('active', 'suspended', 'inactive', 'frozen') DEFAULT 'active'"
        );
    }

    public function down(): void
    {
        // Revert anyone currently 'frozen' back to 'suspended' before shrinking the enum,
        // otherwise the ALTER TABLE itself will fail on existing rows.
        DB::statement("UPDATE users SET status = 'suspended' WHERE status = 'frozen'");
        DB::statement(
            "ALTER TABLE users MODIFY status ENUM('active', 'suspended', 'inactive') DEFAULT 'active'"
        );
    }
};