<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreTraderBotFieldsToQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pi_questions', function (Blueprint $table) {
            $table->integer('max_shares_owned')->nullable();
            $table->integer('min_shares_owned')->nullable();
            $table->integer('max_open_orders')->nullable();
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
            $table->dropColumn('max_shares_owned');
            $table->dropColumn('min_shares_owned');
            $table->dropColumn('max_open_orders');
        });
    }
}
