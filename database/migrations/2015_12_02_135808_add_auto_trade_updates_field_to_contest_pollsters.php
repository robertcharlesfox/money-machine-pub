<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutoTradeUpdatesFieldToContestPollsters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rcp_contest_pollsters', function (Blueprint $table) {
            $table->boolean('auto_trade_updates')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rcp_contest_pollsters', function (Blueprint $table) {
            $table->dropColumn('auto_trade_updates');
        });
    }
}
