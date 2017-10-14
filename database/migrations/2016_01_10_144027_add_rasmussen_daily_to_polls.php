<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRasmussenDailyToPolls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rcp_contest_polls', function (Blueprint $table) {
            $table->decimal('rasmussen_daily_estimate', 5, 2)->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rcp_contest_polls', function (Blueprint $table) {
            $table->dropColumn('rasmussen_daily_estimate');
        });
    }
}
