<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['investor', 'admin'])->default('investor');
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('tier')->default('starter'); // starter, professional, elite, institutional
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->rememberToken();
            $table->timestamps();
            
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};