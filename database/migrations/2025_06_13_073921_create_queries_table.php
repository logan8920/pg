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
        Schema::create('queries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pgtxn_id');
            $table->json('client_request')->nullable();
            $table->json('pg_request')->nullable();
            $table->json('pg_response')->nullable();
            $table->json('client_response')->nullable();
            $table->timestamps();
            $table->foreign('pgtxn_id')->references('id')->on('pgtxns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queries');
    }
};
