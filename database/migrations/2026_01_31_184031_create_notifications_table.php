<?php
// LOCATION: database/migrations/2024_01_01_000007_create_announcements_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('content');
            $table->text('message')->nullable();             // alias for content
            $table->enum('type', ['info', 'warning', 'success', 'danger'])->default('info');
            $table->boolean('is_active')->default(true);

            // Created by admin
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
