<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_shoe_detail');
            $table->unsignedBigInteger('id_sucursal_from');
            $table->unsignedBigInteger('id_sucursal_to');
            $table->unsignedSmallInteger('qty');
            $table->unsignedSmallInteger('status')->default($value = 0);
            $table->timestamps();

            $table->foreign('id_shoe_detail')->references('id')->on('shoe_details');
            $table->foreign('id_sucursal_from')->references('id')->on('sucursals');
            $table->foreign('id_sucursal_to')->references('id')->on('sucursals');
            
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_movements');
    }
}
