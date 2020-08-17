<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPaymentMethodCardOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_payment_method_card_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_card')->nullable($value = false);
            $table->unsignedSmallInteger('installments')->nullable($value = false);
            $table->float('charge', 10, 2)->nullable($value = false);

            $table->timestamps();
            $table->foreign('id_card')->references('id')->on('order_payment_method_cards');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_payment_method_card_options');
    }
}
