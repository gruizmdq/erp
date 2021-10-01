<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTiendaNubeStockErrorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tienda_nube_stock_errors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_shoe_detail');
            $table->string('action');

            $table->timestamps();

            $table->foreign('id_shoe_detail')->references('id')->on('shoe_details');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tienda_nube_stock_errors');
    }
}
