<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImpliedPollBiasToContestsAndQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pi_contests', function (Blueprint $table) {
            $table->decimal('implied_bias', 6, 1)->nullable();
            $table->decimal('implied_variance', 6, 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pi_contests', function (Blueprint $table) {
            $table->dropColumn('implied_bias');
            $table->dropColumn('implied_variance');
        });
    }
}
