<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeMichanikosScrapeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('micha_obama_scrapes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('pollster_name');
            $table->timestamp('micha_timestamp');
            $table->string('date_range');
            $table->string('event_type');
            $table->integer('percent_approval');
            $table->integer('percent_disapproval');
            $table->decimal('rcp_avg_approval', 5, 1);
            $table->decimal('rcp_avg_disapproval', 5, 1);

            $table->integer('rcp_contest_pollster_id')->unsigned();
            $table->foreign('rcp_contest_pollster_id')->references('id')->on('rcp_contest_pollsters');
            $table->integer('rcp_contest_poll_id')->unsigned();
            $table->foreign('rcp_contest_poll_id')->references('id')->on('rcp_contest_polls');
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->date('date_added_to_rcp_average')->nullable();
            $table->date('date_dropped_from_rcp_average')->nullable();

            $table->decimal('rcp_change', 5, 1)->nullable();
            $table->integer('poll_change')->nullable();
            $table->integer('age_of_poll_when_dropped_from_rcp')->nullable();
            $table->integer('days_since_last_poll')->nullable();
            $table->string('day_of_week_added_to_rcp')->nullable();
            $table->string('day_of_week_dropped_from_rcp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('micha_obama_scrapes');
    }
}
