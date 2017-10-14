<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCancelFirstFieldToNazgul extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nazguls', function (Blueprint $table) {
            $table->boolean('cancel_first')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nazguls', function (Blueprint $table) {
            $table->dropColumn('cancel_first');
        });
    }
}
