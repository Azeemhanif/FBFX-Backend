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
        Schema::create('post_signals', function (Blueprint $table) {
            $table->id();
            $table->string('currency_pair')->nullable();
            $table->string('action')->nullable();
            $table->string('stop_loss')->nullable();
            $table->string('profit_one')->nullable();
            $table->string('profit_two')->nullable();
            $table->string('profit_three')->nullable();
            $table->string('RRR')->nullable();
            $table->string('timeframe')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_signals');
    }
};
