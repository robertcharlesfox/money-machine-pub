<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFundraisingFieldsToPiContests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pi_contests', function (Blueprint $table) {
            $table->string('fundraising_committee')->nullable();
            $table->string('fundraising_month')->nullable();
            $table->string('fundraising_description')->nullable();
            $table->timestamp('fundraising_report_filed_timestamp')->nullable();
        });

        Schema::table('pi_questions', function (Blueprint $table) {
            $table->decimal('fundraising_high', 12, 2)->nullable();
            $table->decimal('fundraising_low', 12, 2)->nullable();
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
            $table->dropColumn('fundraising_committee');
            $table->dropColumn('fundraising_month');
            $table->dropColumn('fundraising_description');
            $table->dropColumn('fundraising_report_filed_timestamp');
        });
        Schema::table('pi_questions', function (Blueprint $table) {
            $table->dropColumn('fundraising_high');
            $table->dropColumn('fundraising_low');
        });
    }
}
