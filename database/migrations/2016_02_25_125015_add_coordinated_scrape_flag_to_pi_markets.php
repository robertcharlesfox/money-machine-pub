<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCoordinatedScrapeFlagToPiMarkets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pi_markets', function (Blueprint $table) {
            $table->boolean('is_from_coordinated_scrape')->nullable();
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
            $table->dropColumn('is_from_coordinated_scrape');
        });
    }
}
