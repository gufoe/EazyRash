<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function ($table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('conference_id')->unsigned();

            $table->foreign('user_id')
                ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('conference_id')
                ->references('id')->on('conferences')->onDelete('cascade');
        });

        Schema::create('commits', function ($table) {
            $table->increments('id');
            $table->integer('article_id')->unsigned();

            $table->string('title');
            $table->text('content');

            $table->foreign('article_id')
                ->references('id')->on('articles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('commits');
        Schema::drop('articles');
    }
}
