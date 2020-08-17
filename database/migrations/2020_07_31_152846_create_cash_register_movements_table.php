<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashRegisterMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_register_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_turn');
            $table->boolean('type'); //0 egreso, 1 ingreso
            $table->float('amount', 10, 2);
            $table->string('description', 255);

            $table->timestamps();
            $table->foreign('id_turn')->references('id')->on('cash_register_turns');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_register_movements');
    }
}
