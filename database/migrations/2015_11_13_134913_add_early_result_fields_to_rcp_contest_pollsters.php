<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEarlyResultFieldsToRcpContestPollsters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rcp_contest_pollsters', function (Blueprint $table) {
            $table->integer('early_Clinton')->nullable();
            $table->integer('early_Sanders')->nullable();
            $table->integer('early_OMalley')->nullable();

            $table->integer('early_Trump')->nullable();
            $table->integer('early_Carson')->nullable();
            $table->integer('early_Bush')->nullable();
            $table->integer('early_Rubio')->nullable();
            $table->integer('early_Cruz')->nullable();
            $table->integer('early_Fiorina')->nullable();
            $table->integer('early_Huckabee')->nullable();
            $table->integer('early_Paul')->nullable();
            $table->integer('early_Kasich')->nullable();
            $table->integer('early_Christie')->nullable();
            $table->integer('early_Santorum')->nullable();
            $table->integer('early_Jindal')->nullable();
            $table->integer('early_Graham')->nullable();
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
            $table->dropColumn('early_Clinton');
            $table->dropColumn('early_Sanders');
            $table->dropColumn('early_OMalley');

            $table->dropColumn('early_Trump');
            $table->dropColumn('early_Carson');
            $table->dropColumn('early_Bush');
            $table->dropColumn('early_Rubio');
            $table->dropColumn('early_Cruz');
            $table->dropColumn('early_Fiorina');
            $table->dropColumn('early_Huckabee');
            $table->dropColumn('early_Paul');
            $table->dropColumn('early_Kasich');
            $table->dropColumn('early_Christie');
            $table->dropColumn('early_Santorum');
            $table->dropColumn('early_Jindal');
            $table->dropColumn('early_Graham');
        });
    }
}
