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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('txnid')->unique();
            $table->string('transfertype');
            $table->text('remarks')->nullable();
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('userid');
            $table->decimal('charges', 10, 2)->default(0.00);
            $table->decimal('gst', 10, 2)->default(0.00);
            $table->decimal('profit', 10, 2)->default(0.00);
            $table->unsignedBigInteger('mode_id');
            $table->unsignedBigInteger('pg_company_id');
            $table->string('utr')->nullable(); // bank transaction id
            $table->timestamp('dateupdated')->nullable();
            $table->timestamps();
            $table->foreign(columns: 'user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pg_company_id')->references('id')->on('pg_companies')->onDelete('cascade');
            $table->foreign('mode_id')->references('id')->on('modes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
