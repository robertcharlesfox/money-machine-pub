<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCacheFieldsToPiMarket extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pi_markets', function (Blueprint $table) {
            $table->integer('market_support_yes_side_price')->nullable();
            $table->integer('market_support_no_side_price')->nullable();
            $table->integer('market_support_yes_side_dollars')->nullable();
            $table->integer('market_support_no_side_dollars')->nullable();
            $table->integer('market_support_net_price_spread')->nullable();
            $table->integer('market_support_net_dollars')->nullable();
            $table->integer('market_support_ratio_price')->nullable();
            $table->integer('market_support_ratio_dollars')->nullable();
            $table->integer('current_shares')->nullable();
            $table->integer('current_average_price')->nullable();
            $table->boolean('current_position_is_yes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pi_markets', function (Blueprint $table) {
            $table->dropColumn('market_support_yes_side_price');
            $table->dropColumn('market_support_no_side_price');
            $table->dropColumn('market_support_yes_side_dollars');
            $table->dropColumn('market_support_no_side_dollars');
            $table->dropColumn('market_support_net_price_spread');
            $table->dropColumn('market_support_net_dollars');
            $table->dropColumn('market_support_ratio_price');
            $table->dropColumn('market_support_ratio_dollars');
            $table->dropColumn('current_shares');
            $table->dropColumn('current_average_price');
            $table->dropColumn('current_position_is_yes');
        });
    }
}
