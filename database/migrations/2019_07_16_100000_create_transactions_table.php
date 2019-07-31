<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('payment_model')->nullable()->default(null);
            $table->string('payment_record')->nullable()->default(null);
            $table->string('payment_column')->nullable()->default(null);
            $table->enum('payment_action', ['add', 'subtract', 'set'])->default('set');
            $table->float('amount');
            $table->string('instrument')->nullable();
            $table->string('poll_url')->nullable();
            $table->boolean('paid')->default(false);
            $table->string('value')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transactions');
    }
}
