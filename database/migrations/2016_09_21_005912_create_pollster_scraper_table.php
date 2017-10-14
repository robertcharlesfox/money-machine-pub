<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePollsterScraperTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rcp_pollsters', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->boolean('active')->default(1);
            $table->string('name');
        });

        Schema::create('join_pollsters_contest_pollsters', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->boolean('active')->default(1);
            $table->integer('rcp_pollster_id')->unsigned();
            $table->foreign('rcp_pollster_id')->references('id')->on('rcp_pollsters');
            $table->integer('rcp_contest_pollster_id')->unsigned();
            $table->foreign('rcp_contest_pollster_id')->references('id')->on('rcp_contest_pollsters');
        });

        Schema::create('rcp_pollster_scrapers', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->boolean('active')->default(1);
            $table->integer('rcp_pollster_id')->unsigned();
            $table->foreign('rcp_pollster_id')->references('id')->on('rcp_pollsters');
            $table->string('scraper_job_class_name');
            $table->integer('scrapes_per_hour')->nullable();
            $table->integer('scrapes_per_minute')->nullable();
            $table->text('notes')->nullable();
            $table->time('time_start_1')->nullable();
            $table->time('time_start_2')->nullable();
            $table->time('time_start_3')->nullable();
            $table->time('time_end_1')->nullable();
            $table->time('time_end_2')->nullable();
            $table->time('time_end_3')->nullable();
            $table->boolean('scrape_monday')->default(0);
            $table->boolean('scrape_tuesday')->default(0);
            $table->boolean('scrape_wednesday')->default(0);
            $table->boolean('scrape_thursday')->default(0);
            $table->boolean('scrape_friday')->default(0);
            $table->boolean('scrape_saturday')->default(0);
            $table->boolean('scrape_sunday')->default(0);
        });

        Schema::create('join_pollster_scrapers_contest_pollsters', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->boolean('active')->default(1);
            $table->integer('rcp_pollster_scraper_id')->unsigned();
            // $table->foreign('rcp_pollster_scraper_id')->references('id')->on('rcp_pollster_scrapers');
            $table->integer('rcp_contest_pollster_id')->unsigned();
            // $table->foreign('rcp_contest_pollster_id')->references('id')->on('rcp_contest_pollsters');
        });

        Schema::create('rcp_pollster_scraper_finds', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('rcp_pollster_scraper_id')->unsigned();
            // $table->foreign('rcp_pollster_scraper_id')->references('id')->on('rcp_pollster_scrapers');
            $table->boolean('is_relevant')->default(1);
            $table->string('url');
        });

        Schema::create('join_polls_pollster_scraper_finds', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->boolean('active')->default(1);
            $table->integer('rcp_contest_poll_id')->unsigned();
            // $table->foreign('rcp_contest_poll_id')->references('id')->on('rcp_contest_polls');
            $table->integer('rcp_pollster_scraper_find_id')->unsigned();
            // $table->foreign('rcp_pollster_scraper_find_id')->references('id')->on('rcp_pollster_scraper_finds');
            $table->date('poll_date_start')->nullable();
            $table->date('poll_date_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('join_polls_pollster_scraper_finds');
        Schema::drop('rcp_pollster_scraper_finds');
        Schema::drop('join_pollster_scrapers_contest_pollsters');
        Schema::drop('rcp_pollster_scrapers');
        Schema::drop('join_pollsters_contest_pollsters');
        Schema::drop('rcp_pollsters');
    }
}
