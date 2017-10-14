<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateElectionNightDataTrackers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('election_states', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->string('name_short');
            $table->integer('rank_total');
            $table->string('electoral_method');
            $table->integer('electoral_votes');
            $table->string('time_polls_close');
            $table->date('early_vote_begins')->nullable();
            $table->integer('percent_white')->nullable();
            $table->integer('percent_black')->nullable();
            $table->integer('percent_bachelors')->nullable();
            $table->integer('votes_dem_2012')->nullable();
            $table->integer('votes_gop_2012')->nullable();
            $table->integer('votes_total_2012')->nullable();
            $table->integer('votes_dem_2008')->nullable();
            $table->integer('votes_gop_2008')->nullable();
            $table->integer('votes_total_2008')->nullable();
            $table->integer('votes_dem_1996')->nullable();
            $table->integer('votes_gop_1996')->nullable();
            $table->integer('votes_total_1996')->nullable();
            $table->integer('R_Senators_not_on_ballot')->nullable();
            $table->integer('non_R_Senators_not_on_ballot')->nullable();
            $table->string('group')->nullable();
        });

        Schema::create('election_races', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->boolean('active')->default(1);
            $table->integer('election_state_id')->unsigned();
            $table->foreign('election_state_id')->references('id')->on('election_states');
            $table->string('office');
            $table->string('district_number')->nullable();
            $table->string('incumbent_party')->nullable();
            $table->string('party_id_1')->nullable();
            $table->string('party_id_2')->nullable();
            $table->string('party_id_3')->nullable();
            $table->string('winner_predicted')->nullable();
            $table->string('winner_called')->nullable();
            $table->integer('dem_chance_predicted')->nullable();
            $table->integer('percent_white')->nullable();
            $table->integer('percent_black')->nullable();
            $table->integer('percent_over_18')->nullable();
            $table->integer('percent_bachelors')->nullable();
            $table->integer('median_income')->nullable();
            $table->integer('votes_dem_last_time')->nullable();
            $table->integer('votes_gop_last_time')->nullable();
            $table->integer('votes_total_last_time')->nullable();
            $table->integer('campaign_raised')->nullable();
            $table->integer('campaign_spent')->nullable();
            $table->integer('outside_support_spent')->nullable();
            $table->integer('outside_opposition_spent')->nullable();
            $table->integer('huffpo_poll_average')->nullable();
            $table->integer('rcp_poll_average')->nullable();
            $table->string('rcp_url')->nullable();
            $table->string('rating_cook')->nullable();
            $table->string('rating_sabato')->nullable();
            $table->string('rating_rollcall')->nullable();
            $table->string('rating_ballotpedia')->nullable();
            $table->string('rating_538')->nullable();
            $table->boolean('is_dem_target')->default(0);
            $table->boolean('is_gop_target')->default(0);
            $table->boolean('is_obama_endorsed')->default(0);

            // cached totals of the increments
            $table->integer('votes_dem_cached')->default(0);
            $table->integer('votes_gop_cached')->default(0);
            $table->integer('votes_independent_cached')->default(0);
            $table->integer('votes_libertarian_cached')->default(0);
            $table->integer('votes_others_cached')->default(0);

            // Is this a Contest or Question? If Question, based on Dem or Rep?
            $table->string('pi_contest_type')->nullable();
            $table->integer('pi_contest_id')->unsigned()->nullable();
            $table->string('pi_last_price')->nullable();

            $table->string('group')->nullable();
        });

        Schema::create('election_result_increments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('election_race_id')->unsigned();
            $table->foreign('election_race_id')->references('id')->on('election_races');
            $table->integer('votes_dem_total');
            $table->integer('votes_gop_total');
            $table->integer('votes_independent_total');
            $table->integer('votes_libertarian_total');
            $table->integer('votes_others_total');
            $table->integer('votes_dem_increment');
            $table->integer('votes_gop_increment');
            $table->integer('votes_independent_increment');
            $table->integer('votes_libertarian_increment');
            $table->integer('votes_others_increment');
            $table->string('data_source');
            $table->boolean('ignore_me')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('election_result_increments');
        Schema::drop('election_races');
        Schema::drop('election_states');
    }
}
