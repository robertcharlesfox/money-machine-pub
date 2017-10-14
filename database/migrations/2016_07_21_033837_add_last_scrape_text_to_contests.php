<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastScrapeTextToContests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pi_contests', function (Blueprint $table) {
            $table->text('last_rcp_scrape_table_1')->nullable();
            $table->text('last_rcp_scrape_table_2')->nullable();
            $table->text('last_rcp_scrape_other')->nullable();
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
            $table->dropColumn('last_rcp_scrape_table_1');
            $table->dropColumn('last_rcp_scrape_table_2');
            $table->dropColumn('last_rcp_scrape_other');
        });
    }
}
