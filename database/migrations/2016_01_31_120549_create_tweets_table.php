<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTweetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tweets', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->boolean('is_processed')->default(0);
            $table->text('tweet_raw_data')->nullable();
            $table->integer('twitter_tweet_id')->unsigned()->nullable();
            $table->integer('twitter_user_id')->unsigned()->nullable();
            $table->string('tweet_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tweets');
    }
}
