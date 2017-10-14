<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRcpAddTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rcp_add_trades', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('pi_contest_id')->unsigned();
            $table->foreign('pi_contest_id')->references('id')->on('pi_contests');
            $table->integer('rcp_contest_pollster_id')->unsigned();
            $table->integer('pi_question_id')->unsigned();
            $table->foreign('pi_question_id')->references('id')->on('pi_questions');
            $table->integer('rcp_update_id')->unsigned()->nullable();
            $table->boolean('active')->default(0);
            $table->boolean('auto_trade_me')->default(0);

            $table->integer('poll_result')->default(0);

            $table->integer('price')->default(0);
            $table->integer('shares')->default(0);
            $table->string('yes_or_no')->nullable();
            $table->string('buy_or_sell')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rcp_add_trades');
    }
}
