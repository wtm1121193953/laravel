<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInviteUserRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invite_user_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index()->default(0)->comment('用户ID');
            $table->integer('origin_id')->index()->default(0)->comment('推广人ID(用户ID, 商户ID 或 运营中心ID)');
            $table->tinyInteger('origin_type')->index()->default(0)->comment('推广人类型  1-用户 2-商户 3-运营中心');
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
        Schema::dropIfExists('invite_user_records');
    }
}
