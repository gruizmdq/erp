<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockResetItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_reset_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_stock_reset');
            $table->unsignedBigInteger('id_shoe_detail');
            $table->unsignedBigInteger('old_stock');
            $table->unsignedBigInteger('new_stock');

            $table->timestamps();

            $table->foreign('id_stock_reset')->references('id')->on('stock_resets');
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
        Schema::dropIfExists('stock_reset_items');
    }
}
