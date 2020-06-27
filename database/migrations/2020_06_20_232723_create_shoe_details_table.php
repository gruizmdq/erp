<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoeDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shoe_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_shoe');
            $table->unsignedBigInteger('id_color');
            $table->unsignedBigInteger('number');
            $table->string('barcode')->default($value = null)->unique();
            $table->float('buy_price', 10, 2)->nullable($value = false);
            $table->float('sell_price', 10, 2)->nullable($value = false);
            $table->unsignedSmallInteger('stock');
            $table->boolean('available_tiendanube')->default($value = 0);
            $table->boolean('available_marketplace')->default($value = 0);
            $table->timestamps();

            $table->foreign('id_shoe')->references('id')->on('shoes');
            $table->foreign('id_color')->references('id')->on('shoe_colors');
            $table->unique(['id_shoe', 'id_color', 'number']);

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
        Schema::dropIfExists('shoe_details');
    }
}
