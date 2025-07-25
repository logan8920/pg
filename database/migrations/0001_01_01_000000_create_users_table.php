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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->tinyInteger('api_partner')->default(0);
            $table->string('firmname')->nullable();
            $table->string('business_name')->nullable();
            $table->string('username')->nullable();
            $table->string('phone',12)->nullable();
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->string('commissiontype')->nullable();     // e.g., "fixed" or "percentage"
            $table->unsignedBigInteger('supdistributor')->nullable(); // foreign key id
            $table->unsignedBigInteger('partner')->nullable();        // foreign key id
            $table->unsignedBigInteger('distributor')->nullable();    // foreign key id
            $table->tinyInteger('status')->nullable();
            $table->dateTime('datetime')->nullable();
            $table->integer('created_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
