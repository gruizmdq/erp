<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_resets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_sucursal');
            $table->unsignedBigInteger('id_brand')->nullable();
            $table->boolean('status')->default(0);

            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users');
            $table->foreign('id_sucursal')->references('id')->on('sucursals');
            $table->foreign('id_brand')->references('id')->on('shoe_brands');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_resets');
    }
}
