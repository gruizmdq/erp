<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTiendanubesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_tiendanubes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_tiendanube');
            $table->unsignedBigInteger('number_tiendanube');
            $table->float('shipping_cost_owner', 10, 2);
            $table->float('shipping_cost_customer', 10, 2);
            $table->string('shipping_option')->nullable();
            $table->float('discount', 10, 2);
            $table->string('gateway')->nullable();
            $table->string('comments')->nullable();
            $table->unsignedSmallInteger('status')->default($value = 0);

            $table->timestamps();
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
        Schema::dropIfExists('order_tiendanubes');
    }
}
