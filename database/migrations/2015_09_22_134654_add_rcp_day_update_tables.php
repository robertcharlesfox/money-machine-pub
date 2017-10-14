<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRcpDayUpdateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rcp_days', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->date('rcp_date');
        });

        Schema::create('rcp_updates', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->time('rcp_time');
            $table->timestamp('rcp_timestamp');
            $table->integer('pi_contest_id')->unsigned();
            $table->foreign('pi_contest_id')->references('id')->on('pi_contests');
            $table->integer('rcp_day_id')->unsigned();
            $table->foreign('rcp_day_id')->references('id')->on('rcp_days');
            $table->string('date_range')->nullable();
            $table->integer('date_range_length')->nullable();
            $table->integer('oldest_poll')->nullable();
            $table->integer('count_pollsters')->nullable();
            $table->integer('count_adds')->nullable();
            $table->integer('count_drops')->nullable();
            $table->decimal('percent_approval', 5, 1)->nullable();
            $table->decimal('percent_disapproval', 5, 1)->nullable();
            $table->decimal('Clinton', 5, 1)->nullable();
            $table->decimal('Sanders', 5, 1)->nullable();
            $table->decimal('Biden', 5, 1)->nullable();
            $table->decimal('O\'Malley', 5, 1)->nullable();
            $table->decimal('Webb', 5, 1)->nullable();
            $table->decimal('Chafee', 5, 1)->nullable();
            $table->decimal('Trump', 5, 1)->nullable();
            $table->decimal('Carson', 5, 1)->nullable();
            $table->decimal('Bush', 5, 1)->nullable();
            $table->decimal('Rubio', 5, 1)->nullable();
            $table->decimal('Cruz', 5, 1)->nullable();
            $table->decimal('Fiorina', 5, 1)->nullable();
            $table->decimal('Huckabee', 5, 1)->nullable();
            $table->decimal('Paul', 5, 1)->nullable();
            $table->decimal('Kasich', 5, 1)->nullable();
            $table->decimal('Christie', 5, 1)->nullable();
            $table->decimal('Walker', 5, 1)->nullable();
            $table->decimal('Perry', 5, 1)->nullable();
            $table->decimal('Santorum', 5, 1)->nullable();
            $table->decimal('Jindal', 5, 1)->nullable();
            $table->decimal('Graham', 5, 1)->nullable();
        });

        Schema::create('rcp_update_adds', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('rcp_update_id')->unsigned();
            $table->foreign('rcp_update_id')->references('id')->on('rcp_updates');
            $table->integer('rcp_contest_poll_id')->unsigned();
            $table->foreign('rcp_contest_poll_id')->references('id')->on('rcp_contest_polls');
            $table->integer('rcp_contest_pollster_id')->unsigned();
            $table->foreign('rcp_contest_pollster_id')->references('id')->on('rcp_contest_pollsters');
        });

        Schema::create('rcp_update_drops', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('rcp_update_id')->unsigned();
            $table->foreign('rcp_update_id')->references('id')->on('rcp_updates');
            $table->integer('rcp_contest_poll_id')->unsigned();
            $table->foreign('rcp_contest_poll_id')->references('id')->on('rcp_contest_polls');
            $table->integer('rcp_contest_pollster_id')->unsigned();
            $table->foreign('rcp_contest_pollster_id')->references('id')->on('rcp_contest_pollsters');
        });

        Schema::create('rcp_update_pollsters', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('rcp_update_id')->unsigned();
            $table->foreign('rcp_update_id')->references('id')->on('rcp_updates');
            $table->integer('rcp_contest_poll_id')->unsigned();
            $table->foreign('rcp_contest_poll_id')->references('id')->on('rcp_contest_polls');
            $table->integer('rcp_contest_pollster_id')->unsigned();
            $table->foreign('rcp_contest_pollster_id')->references('id')->on('rcp_contest_pollsters');
        });

        Schema::table('rcp_contest_polls', function (Blueprint $table) {
            $table->string('day_of_week_added_to_rcp')->nullable();
            $table->string('day_of_week_dropped_from_rcp')->nullable();
            $table->integer('age_of_poll_when_dropped_from_rcp')->nullable();
            $table->integer('Clinton')->nullable();
            $table->integer('Sanders')->nullable();
            $table->integer('Biden')->nullable();
            $table->integer('O\'Malley')->nullable();
            $table->integer('Webb')->nullable();
            $table->integer('Chafee')->nullable();
            $table->integer('Trump')->nullable();
            $table->integer('Carson')->nullable();
            $table->integer('Bush')->nullable();
            $table->integer('Rubio')->nullable();
            $table->integer('Cruz')->nullable();
            $table->integer('Fiorina')->nullable();
            $table->integer('Huckabee')->nullable();
            $table->integer('Paul')->nullable();
            $table->integer('Kasich')->nullable();
            $table->integer('Christie')->nullable();
            $table->integer('Walker')->nullable();
            $table->integer('Perry')->nullable();
            $table->integer('Santorum')->nullable();
            $table->integer('Jindal')->nullable();
            $table->integer('Graham')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rcp_update_adds');
        Schema::drop('rcp_update_drops');
        Schema::drop('rcp_update_pollsters');
        Schema::drop('rcp_updates');
        Schema::drop('rcp_days');

        Schema::table('rcp_contest_polls', function (Blueprint $table) {
            $table->dropColumn('day_of_week_added_to_rcp');
            $table->dropColumn('day_of_week_dropped_from_rcp');
            $table->dropColumn('age_of_poll_when_dropped_from_rcp');
            $table->dropColumn('Clinton');
            $table->dropColumn('Sanders');
            $table->dropColumn('Biden');
            $table->dropColumn('O\'Malley');
            $table->dropColumn('Webb');
            $table->dropColumn('Chafee');
            $table->dropColumn('Trump');
            $table->dropColumn('Carson');
            $table->dropColumn('Bush');
            $table->dropColumn('Rubio');
            $table->dropColumn('Cruz');
            $table->dropColumn('Fiorina');
            $table->dropColumn('Huckabee');
            $table->dropColumn('Paul');
            $table->dropColumn('Kasich');
            $table->dropColumn('Christie');
            $table->dropColumn('Walker');
            $table->dropColumn('Perry');
            $table->dropColumn('Santorum');
            $table->dropColumn('Jindal');
            $table->dropColumn('Graham');
        });
    }
}
