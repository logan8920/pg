<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_partner_mode_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('pg_company_id');
            $table->unsignedBigInteger('mode_id');
            $table->decimal("c_per_day_limit")->default(0);
            $table->decimal("mode_limit")->default(0);
            $table->json('charges')->nullable()->comment('charge according to slab');
            $table->timestamps();
            $table->foreign('pg_company_id')->references('id')->on('pg_companies')->onDelete('cascade');
            $table->foreign('mode_id')->references('id')->on('modes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_partner_mode_companies');
    }
};
