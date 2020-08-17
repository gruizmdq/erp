<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->string('order_type');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_client')->nullable($value = true);
            $table->unsignedSmallInteger('qty');
            $table->float('subtotal', 10, 2)->nullable($value = false);
            $table->float('total', 10, 2)->nullable($value = false);
            $table->unsignedBigInteger('id_discount')->nullable($value = true);

            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('id_user')->references('id')->on('users');
            $table->foreign('id_discount')->references('id')->on('order_discounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_user');
    }
}
