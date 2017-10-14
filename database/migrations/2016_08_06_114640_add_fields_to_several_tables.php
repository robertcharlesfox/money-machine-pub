<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToSeveralTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pi_markets', function (Blueprint $table) {
            $table->text('offer_table')->nullable();
        });

        Schema::table('rcp_contest_pollsters', function (Blueprint $table) {
            $table->text('cached_values')->nullable();
        });

        Schema::table('pi_questions', function (Blueprint $table) {
            $table->text('cached_values')->nullable();
        });

        Schema::table('pi_contests', function (Blueprint $table) {
            $table->text('cached_values')->nullable();
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
            $table->dropColumn('offer_table');
        });

        Schema::table('rcp_contest_pollsters', function (Blueprint $table) {
            $table->dropColumn('cached_values');
        });

        Schema::table('pi_questions', function (Blueprint $table) {
            $table->dropColumn('cached_values');
        });

        Schema::table('pi_contests', function (Blueprint $table) {
            $table->dropColumn('cached_values');
        });
    }
}
