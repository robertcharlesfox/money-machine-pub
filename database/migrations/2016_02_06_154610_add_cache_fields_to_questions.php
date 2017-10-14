<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCacheFieldsToQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pi_questions', function (Blueprint $table) {
            $table->integer('cache_total_shares')->nullable();
            $table->integer('cache_todays_volume')->nullable();
            $table->integer('cache_last_trade_price')->nullable();
            $table->integer('cache_market_support_yes_side_price')->nullable();
            $table->integer('cache_market_support_no_side_price')->nullable();
            $table->integer('cache_market_support_yes_side_dollars')->nullable();
            $table->integer('cache_market_support_no_side_dollars')->nullable();
            $table->integer('cache_market_support_net_price_spread')->nullable();
            $table->integer('cache_market_support_net_dollars')->nullable();
            $table->integer('cache_market_support_ratio_price')->nullable();
            $table->integer('cache_market_support_ratio_dollars')->nullable();
            $table->integer('cache_current_shares')->nullable();
            $table->integer('cache_current_average_price')->nullable();
            $table->boolean('cache_current_position_is_yes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pi_questions', function (Blueprint $table) {
            $table->dropColumn('cache_total_shares');
            $table->dropColumn('cache_todays_volume');
            $table->dropColumn('cache_last_trade_price');
            $table->dropColumn('cache_market_support_yes_side_price');
            $table->dropColumn('cache_market_support_no_side_price');
            $table->dropColumn('cache_market_support_yes_side_dollars');
            $table->dropColumn('cache_market_support_no_side_dollars');
            $table->dropColumn('cache_market_support_net_price_spread');
            $table->dropColumn('cache_market_support_net_dollars');
            $table->dropColumn('cache_market_support_ratio_price');
            $table->dropColumn('cache_market_support_ratio_dollars');
            $table->dropColumn('cache_current_shares');
            $table->dropColumn('cache_current_average_price');
            $table->dropColumn('cache_current_position_is_yes');
        });
    }
}
