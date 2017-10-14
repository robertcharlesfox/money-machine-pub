<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewCandidatesToPollsAndUpdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rcp_updates', function (Blueprint $table) {
            $table->string('spread')->nullable();
            $table->decimal('Johnson', 5, 1)->nullable();
            $table->decimal('Stein', 5, 1)->nullable();
        });

        Schema::table('rcp_contest_polls', function (Blueprint $table) {
            $table->string('spread')->nullable();
            $table->integer('Johnson')->nullable();
            $table->integer('Stein')->nullable();
        });

        Schema::table('rcp_contest_pollsters', function (Blueprint $table) {
            $table->string('early_spread')->nullable();
            $table->integer('early_Johnson')->nullable();
            $table->integer('early_Stein')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rcp_updates', function (Blueprint $table) {
            $table->dropColumn('spread');
            $table->dropColumn('Johnson');
            $table->dropColumn('Stein');
        });

        Schema::table('rcp_contest_polls', function (Blueprint $table) {
            $table->dropColumn('spread');
            $table->dropColumn('Johnson');
            $table->dropColumn('Stein');
        });

        Schema::table('rcp_contest_pollsters', function (Blueprint $table) {
            $table->dropColumn('early_spread');
            $table->dropColumn('early_Johnson');
            $table->dropColumn('early_Stein');
        });
    }
}
