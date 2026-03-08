<?php
// database/migrations/2026_03_07_000001_create_task_completions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->timestamp('completed_at')->nullable();
            $table->decimal('reward', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'task_id', 'completed_at'], 'user_task_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_completions');
    }
};