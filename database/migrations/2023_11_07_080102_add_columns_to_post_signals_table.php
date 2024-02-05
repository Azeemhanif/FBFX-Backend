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
            $table->boolean('fvrt')->default(0);
            $table->string('pips')->default(0);
            $table->string('closed')->default('no');
            $table->boolean('worst_pips')->default(0);
            $table->string('runningLivePips')->default(0);
            $table->string('close_price')->nullable();
            $table->string('open_price')->nullable();
            $table->string('role')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->nullable()->references('id')->on('users')->onDelete('cascade');
            $table->string('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_signals', function (Blueprint $table) {
            $table->dropColumn('fvrt');
            $table->dropColumn('closed');
            $table->dropColumn('pips');
            $table->dropColumn('worst_pips');
            $table->dropColumn('close_price');
            $table->dropColumn('open_price');
            $table->dropColumn('user_id');
            $table->dropColumn('role');
            $table->dropColumn('type');
        });
    }
};
