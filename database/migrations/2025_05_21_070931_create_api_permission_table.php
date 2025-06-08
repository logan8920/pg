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
        Schema::create('api_permission', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // or partner_id, if required
            $table->boolean('udio')->default(false);
            $table->boolean('dmt')->default(false);
            $table->boolean('dmt_cash_free')->default(false);
            $table->boolean('dmt_razorpay')->default(false);
            $table->boolean('dmt_pay_ten')->default(false);
            $table->boolean('dmt_paytm')->default(false);
            $table->timestamps();

            // If user_id references the users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_permission');
    }
};
