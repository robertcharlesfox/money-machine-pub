<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRandomPollCounterToPiContests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pi_contests', function (Blueprint $table) {
            $table->integer('random_polls_to_add')->default(0);
            $table->integer('max_shares_to_hold')->default(0);
            $table->integer('shares_per_trade')->default(0);
            $table->integer('shares_in_blocking_bid')->default(0);
            $table->boolean('auto_trade_this_contest')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pi_contests', function (Blueprint $table) {
            $table->dropColumn('random_polls_to_add');
            $table->dropColumn('max_shares_to_hold');
            $table->dropColumn('shares_per_trade');
            $table->dropColumn('shares_in_blocking_bid');
            $table->dropColumn('auto_trade_this_contest');
        });
    }
}
