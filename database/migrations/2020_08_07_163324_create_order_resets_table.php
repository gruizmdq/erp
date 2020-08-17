<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_resets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_seller');
            $table->unsignedBigInteger('id_cashier');
            $table->unsignedBigInteger('id_sucursal');

            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users');
            $table->foreign('id_seller')->references('id')->on('users');
            $table->foreign('id_cashier')->references('id')->on('users');
            $table->foreign('id_sucursal')->references('id')->on('sucursals');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_resets');
    }
}
