<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablesForPollScraping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // A Scrape of a Contest's RCP Average and flags for whether changes have occurred.
        Schema::create('rcp_scrapes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->date('created_date');
            $table->integer('pi_contest_id')->unsigned();
            $table->foreign('pi_contest_id')->references('id')->on('pi_contests');
            $table->decimal('average', 5, 2);
            $table->boolean('has_change_since_last_scrape')->default(0);
            $table->boolean('has_dropouts')->default(0);
            $table->boolean('has_additions')->default(0);
            $table->integer('update_number_today')->default(0);
            $table->string('update_text')->nullable();
            $table->boolean('manual_predict_is_final')->default(0);
        });

        // Names of pollsters who are actively polling a Contest.
        // Flag for whether this report is scrapable and where.
        Schema::create('rcp_contest_pollsters', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('pi_contest_id')->unsigned();
            $table->foreign('pi_contest_id')->references('id')->on('pi_contests');
            $table->string('name');
            $table->boolean('is_daily')->default(0);
            $table->boolean('is_scrapable')->default(0);
            $table->string('url_for_scraping')->nullable();
            $table->string('scrape_instructions')->nullable();
        });

        // The actual polling data published by a pollster.
        Schema::create('rcp_contest_polls', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('rcp_contest_pollster_id')->unsigned();
            $table->foreign('rcp_contest_pollster_id')->references('id')->on('rcp_contest_pollsters');
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->string('sample')->nullable();
            $table->decimal('percent_favor', 5, 2)->nullable();
            $table->decimal('percent_against', 5, 2)->nullable();
            $table->string('url_source_full_report')->nullable();
            $table->date('date_added_to_rcp_average')->nullable();
            $table->date('date_dropped_from_rcp_average')->nullable();
        });

        // Unit to link a RCP Average with its Contest Pollsters as of a Scrape (a point in time).
        Schema::create('rcp_scrape_pollsters', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('rcp_scrape_id')->unsigned();
            $table->foreign('rcp_scrape_id')->references('id')->on('rcp_scrapes');
            $table->integer('rcp_contest_pollster_id')->unsigned();
            $table->foreign('rcp_contest_pollster_id')->references('id')->on('rcp_contest_pollsters');
            $table->integer('rcp_contest_poll_id')->unsigned();
            $table->foreign('rcp_contest_poll_id')->references('id')->on('rcp_contest_polls');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rcp_scrape_pollsters');
        Schema::drop('rcp_contest_polls');
        Schema::drop('rcp_contest_pollsters');
        Schema::drop('rcp_scrapes');
    }
}
