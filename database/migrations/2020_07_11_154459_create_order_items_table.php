<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_order');
            $table->unsignedBigInteger('id_shoe_detail');
            $table->unsignedSmallInteger('qty');
            $table->float('sell_price', 10, 2)->nullable($value = false);
            $table->float('buy_price', 10, 2)->nullable($value = false);
            $table->float('total', 10, 2)->nullable($value = false);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_order')->references('order_id')->on('orders');
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
        Schema::dropIfExists('order_items');
    }
}
