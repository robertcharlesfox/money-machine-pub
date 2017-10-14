<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreDataTrackingFieldsToPollsters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rcp_contest_pollsters', function (Blueprint $table) {
            $table->boolean('keep_scraping')->default(0);
            $table->boolean('debate_eligible_poll')->default(0);
            $table->date('next_poll_expected')->nullable();
            $table->string('comments')->nullable();
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
            $table->dropColumn('keep_scraping');
            $table->dropColumn('debate_eligible_poll');
            $table->dropColumn('next_poll_expected');
            $table->dropColumn('comments');
        });
    }
}
