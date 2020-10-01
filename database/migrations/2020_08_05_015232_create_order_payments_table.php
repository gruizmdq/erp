<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_order');
            $table->unsignedBigInteger('id_payment_method');
            $table->unsignedBigInteger('id_payment_card')->nullable($value = true);
            $table->unsignedBigInteger('id_payment_option')->nullable($value = true);
            $table->string('coupon');
            $table->float('total', 10, 2);
            
            $table->timestamps();

            $table->foreign('id_order')->references('order_id')->on('orders');
            $table->foreign('id_payment_method')->references('id')->on('order_payment_methods');
            $table->foreign('id_payment_card')->references('id')->on('order_payment_method_cards');
            $table->foreign('id_payment_option')->references('id')->on('order_payment_method_card_options');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_payments');
    }
}
