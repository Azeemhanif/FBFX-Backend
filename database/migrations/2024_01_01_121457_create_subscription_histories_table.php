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
        Schema::create('subscription_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->unsignedBigInteger('team_id')->nullable();
            // $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->string('package_id')->nullable();
            // $table->foreign('package_id')->references('id')->on('memberships');
            $table->boolean('add_by_admin')->default(0);
            $table->enum('subscription_type', ['monthly', 'yearly'])->default('monthly');
            $table->string('device_type', 30)->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('purchase_token')->nullable();
            $table->longText('receipt')->nullable();
            $table->string('customer_token')->nullable();
            $table->string('in_app_id')->nullable();
            $table->boolean('auto_renew')->default(0);
            $table->float('amount');
            $table->dateTime('start_date', $precision = 0)->useCurrent();
            $table->dateTime('end_date', $precision = 0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_histories');
    }
};
