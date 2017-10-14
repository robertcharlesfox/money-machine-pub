<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreToElectionRace extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('election_races', function (Blueprint $table) {
            $table->string('dem_name')->nullable();
            $table->string('gop_name')->nullable();
            $table->string('pi_last_dem')->nullable();
            $table->string('pi_bid_dem')->nullable();
            $table->string('pi_ask_dem')->nullable();
            $table->string('pi_last_gop')->nullable();
            $table->string('pi_bid_gop')->nullable();
            $table->string('pi_ask_gop')->nullable();
            $table->string('pi_bid_question')->nullable();
            $table->string('pi_ask_question')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('election_races', function (Blueprint $table) {
            $table->dropColumn('dem_name');
            $table->dropColumn('gop_name');
            $table->dropColumn('pi_last_dem');
            $table->dropColumn('pi_bid_dem');
            $table->dropColumn('pi_ask_dem');
            $table->dropColumn('pi_last_gop');
            $table->dropColumn('pi_bid_gop');
            $table->dropColumn('pi_ask_gop');
            $table->dropColumn('pi_bid_question');
            $table->dropColumn('pi_ask_question');
        });
    }
}
