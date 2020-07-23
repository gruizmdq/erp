<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderFacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_facturas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_payment_method');
            $table->char('type', 1);
            $table->string('cuit')->nullable($value = true);
            $table->float('subtotal', 10, 2)->nullable($value = false);
            $table->float('iva', 10, 2)->nullable($value = false);
            $table->float('total', 10, 2)->nullable($value = false);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_payment_method')->references('id')->on('order_payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_facturas');
    }
}
