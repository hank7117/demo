<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeatherforecastsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weatherforecasts', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('cid');
            $table->unsignedBigInteger('wid');
            $table->foreign('cid')->references('cid')->on('countries');
            $table->foreign('wid')->references('id')->on('weather');
            $table->tinyInteger('min_temperature');
            $table->tinyInteger('max_temperature');
            $table->tinyInteger('category')->comment("1: day, 2: night");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('weatherforecasts');
    }
}
