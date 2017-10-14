<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGallupDailyTrackingFieldsToPolls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rcp_contest_polls', function (Blueprint $table) {
            $table->decimal('gallup_daily_confirmed', 5, 2)->unsigned()->nullable();
            $table->decimal('gallup_daily_estimate', 5, 2)->unsigned()->nullable();
            $table->boolean('gallup_estimate_is_from_source')->default(0);
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
            $table->dropColumn('gallup_daily_confirmed');
            $table->dropColumn('gallup_daily_estimate');
            $table->dropColumn('gallup_estimate_is_from_source');
        });
    }
}
