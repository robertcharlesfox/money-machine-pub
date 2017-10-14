<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExecutedAutotradeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('executed_autotrades', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->dateTime('trade_executed_datetime');
            $table->string('question_ticker');
            $table->integer('pi_question_id')->unsigned();
            $table->foreign('pi_question_id')->references('id')->on('pi_questions');
            $table->string('action');
            $table->integer('shares');
            $table->integer('price');
            $table->decimal('profit', 5, 2);
            $table->decimal('fees', 5, 3);
            $table->decimal('risk_adjustment', 5, 3);
            $table->decimal('credit', 5, 3);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('executed_autotrades');
    }
}
