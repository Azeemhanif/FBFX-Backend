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
        Schema::table('post_signals', function (Blueprint $table) {
            $table->boolean('tp1_status')->default(0);
            $table->boolean('tp2_status')->default(0);
            $table->boolean('tp3_status')->default(0);
            $table->string('close_price_status')->nullable();
            $table->string('stop_loss_status')->nullable();
            $table->string('currency')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('post_signals', function (Blueprint $table) {
            $table->dropColumn('tp1_status');
            $table->dropColumn('tp2_status');
            $table->dropColumn('tp3_status');
            $table->dropColumn('stop_loss_status');
        });
    }
};
