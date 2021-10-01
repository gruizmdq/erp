<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoeSucursalItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shoe_sucursal_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_shoe_detail');
            $table->unsignedBigInteger('id_sucursal');
            $table->signedBigInteger('stock');
            $table->timestamps();

            $table->foreign('id_shoe_detail')->references('id')->on('shoe_details');
            $table->foreign('id_sucursal')->references('id')->on('sucursals');
            $table->unique(['id_shoe_detail', 'id_sucursal']);
            
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
        Schema::dropIfExists('shoe_sucursal_items');
    }
}
