<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderMarketplacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_marketplaces', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_sucursal')->default($value = 1);
            $table->unsignedBigInteger('id_seller');
            $table->string('client');
            $table->string('phone');
            $table->string('address');
            $table->string('zone');
            $table->float('delivery', 10, 2)->default($value = 0);
            $table->string('comments')->nullable();
            
            $table->timestamps();

            $table->softDeletes();
            $table->foreign('id_sucursal')->references('id')->on('sucursals');
            $table->foreign('id_seller')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_marketplaces');
    }
}
