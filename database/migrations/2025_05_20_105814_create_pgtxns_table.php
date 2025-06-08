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
        Schema::create('pgtxns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('txnid')->nullable()->index();
            $table->string('refid', 255)->nullable()->index();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('charge', 10, 2)->nullable();
            $table->decimal('profit', 5, 2)->nullable();
            $table->string('api', 10)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->tinyInteger('status')->nullable()->comment('0-Failed, 1-Success, 2-Initiated, 3-Complete');
            $table->string('email', 100)->nullable();
            $table->unsignedBigInteger('mode_id');
            $table->unsignedBigInteger('pg_company_id');
            $table->date('addeddate')->nullable();
            $table->timestamp('dateadded')->nullable()->useCurrent();
            $table->dateTime('dateupdated')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
            $table->tinyInteger('refunded')->nullable();
            $table->integer('refundtxnid')->nullable();
            $table->dateTime('daterefunded')->nullable();
            $table->integer('processby', false, true)->length(8)->nullable();
            $table->string('ipaddress', 50)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->text('refund_remarks')->nullable();
            $table->string('utr', 20)->nullable();
            $table->string('order_id', 50)->nullable();
            $table->string('card', 16)->nullable();
            $table->string('sub_type', 50)->nullable();
            $table->string('name', 50)->nullable();
            $table->text('encdata');
            $table->timestamps();

            // Indexes
            $table->index('addeddate');

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
        Schema::dropIfExists('pgtxns');
    }
};
