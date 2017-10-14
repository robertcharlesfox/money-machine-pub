<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRcpDropTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rcp_drop_trades', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('pi_contest_id')->unsigned();
            $table->foreign('pi_contest_id')->references('id')->on('pi_contests');
            $table->integer('rcp_contest_pollster_id_1')->unsigned();
            // $table->foreign('rcp_contest_pollster_id_1')->references('id')->on('rcp_contest_pollsters');
            $table->integer('rcp_contest_pollster_id_2')->unsigned()->nullable();
            // $table->foreign('rcp_contest_pollster_id_2')->references('id')->on('rcp_contest_pollsters');
            $table->integer('rcp_contest_pollster_id_3')->unsigned()->nullable();
            // $table->foreign('rcp_contest_pollster_id_3')->references('id')->on('rcp_contest_pollsters');
            $table->integer('rcp_contest_pollster_id_4')->unsigned()->nullable();
            // $table->foreign('rcp_contest_pollster_id_4')->references('id')->on('rcp_contest_pollsters');
            $table->integer('pi_question_id')->unsigned();
            $table->foreign('pi_question_id')->references('id')->on('pi_questions');
            $table->integer('rcp_update_id')->unsigned()->nullable();
            // $table->foreign('rcp_update_id')->references('id')->on('rcp_updates');
            $table->boolean('active')->default(0);
            $table->boolean('auto_trade_me')->default(0);

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
        Schema::drop('rcp_drop_trades');
    }
}
