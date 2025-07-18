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
        Schema::create('user_pg_credentials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Api Partner ID');
            $table->unsignedBigInteger('pg_id')->comment('Payment Gateway ID');
            $table->text('pg_credentials')->nullable()->comment('Pg Credentials of Api Partner');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pg_id')->references('id')->on('pg_companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_pg_credentials');
    }
};
