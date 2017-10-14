<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRcpCandidateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rcp_candidate_scrapes', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        Schema::create('rcp_candidate_dem_updates', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('date_range');
            $table->decimal('Clinton', 5, 1)->nullable();
            $table->decimal('Sanders', 5, 1)->nullable();
            $table->decimal('Biden', 5, 1)->nullable();
            $table->decimal('O\'Malley', 5, 1)->nullable();
            $table->decimal('Webb', 5, 1)->nullable();
            $table->decimal('Chafee', 5, 1)->nullable();
        });

        Schema::create('rcp_candidate_gop_updates', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('date_range');
            $table->decimal('Trump', 5, 1)->nullable();
            $table->decimal('Carson', 5, 1)->nullable();
            $table->decimal('Bush', 5, 1)->nullable();
            $table->decimal('Rubio', 5, 1)->nullable();
            $table->decimal('Cruz', 5, 1)->nullable();
            $table->decimal('Fiorina', 5, 1)->nullable();
            $table->decimal('Huckabee', 5, 1)->nullable();
            $table->decimal('Paul', 5, 1)->nullable();
            $table->decimal('Kasich', 5, 1)->nullable();
            $table->decimal('Christie', 5, 1)->nullable();
            $table->decimal('Walker', 5, 1)->nullable();
            $table->decimal('Perry', 5, 1)->nullable();
            $table->decimal('Santorum', 5, 1)->nullable();
            $table->decimal('Jindal', 5, 1)->nullable();
            $table->decimal('Graham', 5, 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rcp_candidate_scrapes');
        Schema::drop('rcp_candidate_dem_updates');
        Schema::drop('rcp_candidate_gop_updates');
    }
}
