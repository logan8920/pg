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
        Schema::create('api_credentials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('ipaddress')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->string('key')->nullable();
            $table->string('iv')->nullable();
            $table->text('token')->nullable();
            $table->tinyInteger('pg')->default(1);
            $table->timestamp('date_added')->nullable();
            $table->date('added_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_credentials');
    }
};
