<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProbabilityToPollsters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rcp_contest_pollsters', function (Blueprint $table) {
            $table->integer('probability_dropped')->nullable();
            $table->integer('probability_updated')->nullable();
            $table->integer('probability_added')->nullable();
            $table->text('release_notes')->nullable();
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
            $table->dropColumn('probability_dropped');
            $table->dropColumn('probability_updated');
            $table->dropColumn('probability_added');
            $table->dropColumn('release_notes');
        });
    }
}
