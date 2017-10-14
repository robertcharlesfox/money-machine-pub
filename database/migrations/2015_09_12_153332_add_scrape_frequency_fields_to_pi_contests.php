<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddScrapeFrequencyFieldsToPiContests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pi_contests', function (Blueprint $table) {
            $table->integer('rcp_scrape_frequency')->default(0);
            $table->boolean('rcp_update_txt_alert')->default(0);
        });

        Schema::table('pi_questions', function (Blueprint $table) {
            $table->integer('pi_scrape_frequency')->default(0);
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
            $table->dropColumn('rcp_scrape_frequency');
            $table->dropColumn('rcp_update_txt_alert');
        });

        Schema::table('pi_questions', function (Blueprint $table) {
            $table->dropColumn('pi_scrape_frequency');
        });
    }
}
