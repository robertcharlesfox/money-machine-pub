<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHuffpoPollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('huffpo_polls', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('huffpo_id')->index();
            $table->string('pollster');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('topic');
            $table->text('result_text');
            $table->json('result_json');
            $table->integer('result_int')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('huffpo_polls');
    }
}
