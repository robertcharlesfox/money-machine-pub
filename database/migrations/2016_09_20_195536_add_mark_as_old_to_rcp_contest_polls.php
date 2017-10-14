<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMarkAsOldToRcpContestPolls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rcp_contest_polls', function (Blueprint $table) {
            $table->boolean('mark_as_old')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rcp_contest_polls', function (Blueprint $table) {
            $table->dropColumn('mark_as_old');
        });
    }
}
