<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMovementItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_movement_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_stock_movement');
            $table->unsignedBigInteger('id_shoe_detail');
            $table->unsignedSmallInteger('qty');

            $table->timestamps();
            $table->foreign('id_stock_movement')->references('id')->on('stock_movements');
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
        Schema::dropIfExists('stock_movement_items');
    }
}
