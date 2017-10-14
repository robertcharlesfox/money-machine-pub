<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInitialScrapeAndMarketTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scrapes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        Schema::create('pi_contests', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->boolean('active')->default(1);
            $table->string('name');
            $table->string('url_of_answer');
            $table->string('category');
        });

        Schema::create('pi_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->boolean('active')->default(1);
            $table->integer('pi_contest_id')->unsigned();
            $table->foreign('pi_contest_id')->references('id')->on('pi_contests');
            $table->string('question_text');
            $table->string('question_ticker');
            $table->string('url_of_market');
            $table->string('category');
            $table->date('date_open');
            $table->date('date_close');
            $table->timestamp('ts_contract_closes')->nullable();
            $table->decimal('poll_high', 5, 2)->nullable();
            $table->decimal('poll_low', 5, 2)->nullable();
            $table->string('answer')->nullable();
        });

        Schema::create('pi_markets', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('pi_contest_id')->unsigned();
            $table->foreign('pi_contest_id')->references('id')->on('pi_contests');
            $table->integer('pi_question_id')->unsigned();
            $table->foreign('pi_question_id')->references('id')->on('pi_questions');
            $table->integer('scrape_id')->unsigned();
            $table->foreign('scrape_id')->references('id')->on('scrapes');
            $table->integer('last_price');
            $table->integer('shares_traded');
            $table->integer('todays_volume');
            $table->integer('total_shares');
            $table->decimal('current_poll', 5, 2)->nullable();
        });

        Schema::create('pi_offers', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('pi_market_id')->unsigned();
            $table->foreign('pi_market_id')->references('id')->on('pi_markets');
            $table->string('action');
            $table->integer('price');
            $table->integer('shares');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('scrapes');
        Schema::drop('pi_contests');
        Schema::drop('pi_questions');
        Schema::drop('pi_markets');
        Schema::drop('pi_offers');
    }
}
