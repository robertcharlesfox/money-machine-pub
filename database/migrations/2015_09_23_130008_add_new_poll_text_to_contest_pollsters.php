<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewPollTextToContestPollsters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rcp_contest_pollsters', function (Blueprint $table) {
            $table->string('new_poll_update_text')->nullable();
            $table->boolean('is_likely_final_for_week')->nullable();
            $table->boolean('is_likely_dropout')->nullable();
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
            $table->dropColumn('new_poll_update_text');
            $table->dropColumn('is_likely_final_for_week');
            $table->dropColumn('is_likely_dropout');
        });
    }
}
