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
            $table->text('google_token')->nullable();
            $table->text('social_token')->nullable();
            $table->text('apple_token')->nullable();
            $table->text('fb_token')->nullable();
            $table->string('reset_password_link')->nullable();
            $table->tinyInteger('is_verified')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_token');
            $table->dropColumn('apple_token');
            $table->dropColumn('social_token');
            $table->dropColumn('fb_token');
            $table->dropColumn('is_verified');
            $table->dropColumn('reset_password_link');
        });
    }
};
