<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLevelChangeRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_level_change_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index()->default(0)->comment('用户ID');
            $table->integer('prev_user_level')->default(0)->comment('改变前的用户等级');
            $table->integer('next_user_level')->default(0)->comment('改变后的用户等级');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_level_change_records');
    }
}
