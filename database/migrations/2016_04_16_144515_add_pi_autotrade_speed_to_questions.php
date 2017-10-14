<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPiAutotradeSpeedToQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pi_questions', function (Blueprint $table) {
            $table->string('pi_autotrade_speed')->nullable();
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
            $table->dropColumn('pi_autotrade_speed');
        });
    }
}
