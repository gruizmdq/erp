<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMappingTiendanubesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mapping_tiendanubes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_shoe_detail');
            $table->string('id_tiendanube');
            $table->string('id_tiendanube_product');
            $table->string('id_tiendanube_store');

            $table->timestamps();
            $table->foreign('id_shoe_detail')->references('id')->on('shoe_details');
            $table->unique(["id_shoe_detail", "id_tiendanube_store"]);
            $table->unique(["id_tiendanube", "id_tiendanube_store"]);
            $table->unique(["id_tiendanube_product", "id_tiendanube_store"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mapping_tiendanubes');
    }
}
