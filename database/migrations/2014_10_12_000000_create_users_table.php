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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('mobile')->nullable();
            $table->string('role')->default('user');
            $table->string('image')->nullable();
            $table->string('experience')->nullable();
            $table->string('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('plan')->nullable();
            $table->integer('otp')->nullable();
            $table->boolean('is_otp_verified')->default(0);
            $table->string('otp_expiry_time')->nullable();
            $table->string('trader_type')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
