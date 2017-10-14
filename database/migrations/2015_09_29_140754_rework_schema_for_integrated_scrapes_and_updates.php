<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReworkSchemaForIntegratedScrapesAndUpdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rcp_updates', function (Blueprint $table) {
            $table->integer('second_oldest_poll')->nullable();
        });

        Schema::drop('rcp_scrape_pollsters');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rcp_updates', function (Blueprint $table) {
            $table->dropColumn('second_oldest_poll');
        });

        Schema::create('rcp_scrape_pollsters', function (Blueprint $table) {
        });

    }
}
