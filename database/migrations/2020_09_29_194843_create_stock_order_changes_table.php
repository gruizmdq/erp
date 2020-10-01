<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockOrderChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_order_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_shoe_detail_old');
            $table->unsignedBigInteger('id_shoe_detail_new');

            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users');
            $table->foreign('id_shoe_detail_new')->references('id')->on('shoe_details');
            $table->foreign('id_shoe_detail_old')->references('id')->on('shoe_details');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_order_changes');
    }
}
