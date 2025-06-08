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
        Schema::create('log_requests', function (Blueprint $table) {
            $table->id();
            $table->string('partner_id'); // stores $userid
            $table->string('reqid')->unique(); // assuming reqid must be unique
            $table->json('request'); // json_encode($decodedata)
            $table->text('body'); // request body
            $table->string('method'); // HTTP method (GET, POST, etc.)
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_requests');
    }
};
