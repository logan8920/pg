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
        Schema::create('pg_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('key_path')->nullable()->comment('multiple key paths ?ifany');
            $table->json('pg_config')->nullable()->comment('Payment Gateway Configuration like Mid, mKey, callbackurl');
            $table->mediumText('service_class_name')->nullable()->comment('Payment Gateway Service Class Name');
            $table->mediumText('service_class_path')->nullable()->comment('Payment Gateway Service Path Contain Class for initiate Payament');
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pg_companies');
    }
};
