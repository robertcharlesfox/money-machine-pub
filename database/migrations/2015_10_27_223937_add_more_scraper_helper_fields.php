<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreScraperHelperFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pi_contests', function (Blueprint $table) {
            $table->string('selenium_url')->nullable();
            $table->string('pollingreport_url')->nullable();
            $table->string('pollingreport_last_story')->nullable();
            $table->integer('pollingreport_scrape_frequency')->nullable();
            $table->integer('pollingreport_update_txt_alert')->nullable();
            $table->decimal('approval_threshold_1', 5, 1)->nullable();
            $table->decimal('approval_threshold_2', 5, 1)->nullable();
            $table->decimal('approval_threshold_3', 5, 1)->nullable();
            $table->decimal('approval_threshold_4', 5, 1)->nullable();
            $table->decimal('approval_threshold_5', 5, 1)->nullable();
        });

        Schema::table('rcp_contest_pollsters', function (Blueprint $table) {
            $table->string('selenium_url')->nullable();
            $table->string('last_scrape_date')->nullable();
            $table->string('last_scrape_title')->nullable();
            $table->string('last_scrape_size')->nullable();
            $table->string('last_scrape_link')->nullable();
            $table->string('last_scrape_other')->nullable();
        });

        Schema::table('pi_questions', function (Blueprint $table) {
            $table->string('auto_trade_me')->default(0);
            $table->string('yes_or_no')->nullable();
            $table->integer('buy_price')->nullable();
            $table->integer('buy_shares')->nullable();
            $table->integer('sell_price')->nullable();
            $table->integer('sell_shares')->nullable();
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
            $table->dropColumn('selenium_url');
            $table->dropColumn('pollingreport_url');
            $table->dropColumn('pollingreport_last_story');
            $table->dropColumn('pollingreport_scrape_frequency');
            $table->dropColumn('pollingreport_update_txt_alert');
            $table->dropColumn('approval_threshold_1');
            $table->dropColumn('approval_threshold_2');
            $table->dropColumn('approval_threshold_3');
            $table->dropColumn('approval_threshold_4');
            $table->dropColumn('approval_threshold_5');
        });

        Schema::table('rcp_contest_pollsters', function (Blueprint $table) {
            $table->dropColumn('selenium_url');
            $table->dropColumn('last_scrape_date');
            $table->dropColumn('last_scrape_title');
            $table->dropColumn('last_scrape_size');
            $table->dropColumn('last_scrape_link');
            $table->dropColumn('last_scrape_other');
        });

        Schema::table('pi_questions', function (Blueprint $table) {
            $table->dropColumn('auto_trade_me');
            $table->dropColumn('yes_or_no');
            $table->dropColumn('buy_price');
            $table->dropColumn('buy_shares');
            $table->dropColumn('sell_price');
            $table->dropColumn('sell_shares');
        });
    }
}
