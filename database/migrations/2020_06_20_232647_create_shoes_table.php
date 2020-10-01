<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shoes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->unsignedBigInteger('id_category')->nullable(true);
            $table->unsignedBigInteger('id_brand');
            $table->timestamps();

            $table->foreign('id_category')->references('id')->on('shoe_categories');
            $table->foreign('id_brand')->references('id')->on('shoe_brands');

            $table->unique(['code', 'id_brand']);

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
        Schema::dropIfExists('shoes');
    }
}
