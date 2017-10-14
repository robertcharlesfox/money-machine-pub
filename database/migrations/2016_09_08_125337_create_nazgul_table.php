<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNazgulTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nazguls', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('pi_contest_id')->unsigned();
            $table->integer('pi_question_id')->unsigned();
            $table->integer('pi_market_id')->unsigned();
            $table->integer('price_limit')->nullable();
            $table->integer('price_re_enter')->nullable();
            $table->integer('percent_certainty')->nullable();
            $table->integer('risk')->nullable();
            $table->integer('shares_limit')->nullable();
            $table->decimal('range_max', 12, 2)->nullable();
            $table->decimal('range_min', 12, 2)->nullable();
            $table->decimal('value_found', 12, 2)->nullable();
            $table->string('yes_or_no')->nullable();
            $table->string('buy_or_sell')->nullable();
            $table->string('competition_category')->nullable();
            $table->boolean('active')->nullable();
            $table->boolean('auto_trade_me')->nullable();
            $table->boolean('executed')->nullable();
            $table->boolean('cancelled')->nullable();
            $table->text('cached_values')->nullable();
            $table->integer('executed_shares')->nullable();
            $table->decimal('executed_dollars', 5, 2)->nullable();
            $table->integer('re_enter_shares')->nullable();
            $table->decimal('re_enter_dollars', 5, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('nazguls');
    }
}
