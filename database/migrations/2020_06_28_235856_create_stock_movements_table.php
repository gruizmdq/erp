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
            $table->unsignedBigInteger('id_sucursal_from');
            $table->unsignedBigInteger('id_sucursal_to');
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('qty');
            $table->unsignedSmallInteger('status')->default($value = 0);
            $table->timestamps();

            $table->foreign('id_sucursal_from')->references('id')->on('sucursals');
            $table->foreign('id_sucursal_to')->references('id')->on('sucursals');
            
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
