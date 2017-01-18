<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CommitsHavePlainTextRashNoFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commits', function ($table) {
            $table->dropForeign('commits_file_id_foreign');
            $table->dropColumn('file_id');
        });
        Schema::table('commits', function ($table) {
            $table->longText('rash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
