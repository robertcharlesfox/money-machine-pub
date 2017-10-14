<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPollsterUpdateFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rcp_contest_pollsters', function (Blueprint $table) {
            $table->string('update_frequency')->nullable();
            $table->integer('un_included_actual_result')->nullable();
            $table->decimal('projected_result', 5, 2);
            $table->dropColumn('is_daily');
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
            $table->dropColumn('update_frequency');
            $table->dropColumn('un_included_actual_result');
            $table->dropColumn('projected_result');
            $table->boolean('is_daily')->nullable();
        });
    }
}
