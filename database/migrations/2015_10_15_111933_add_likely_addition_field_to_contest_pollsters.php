<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLikelyAdditionFieldToContestPollsters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rcp_contest_pollsters', function (Blueprint $table) {
            $table->boolean('is_likely_addition')->nullable();
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
            $table->dropColumn('is_likely_addition');
        });
    }
}
