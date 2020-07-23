<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderSucursalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_sucursals', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_sucursal');
            $table->unsignedBigInteger('id_seller');
            $table->unsignedBigInteger('id_cashier');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_sucursal')->references('id')->on('sucursals');
            $table->foreign('id_seller')->references('id')->on('users');
            $table->foreign('id_cashier')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_sucursals');
    }
}
