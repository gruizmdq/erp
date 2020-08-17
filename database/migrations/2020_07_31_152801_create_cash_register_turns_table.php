<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashRegisterTurnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_register_turns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_cashier');
            $table->unsignedBigInteger('id_cash_register');
            $table->float('start_cash', 10, 2);
            $table->float('end_cash', 10, 2)->nullable($value = true);
            $table->float('gua', 10, 2)->nullable($value = true);
            $table->boolean('status')->default(false);
            $table->float('correction', 10, 2)->nullable($value = true);

            $table->timestamps();
            $table->foreign('id_cashier')->references('id')->on('users');
            $table->foreign('id_cash_register')->references('id')->on('cash_registers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_register_turns');
    }
}
