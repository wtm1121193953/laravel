<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInviteUserStatisticsDailiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invite_user_statistics_dailies', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date')->index()->comment('日期');
            $table->integer('origin_id')->index()->default(0)->comment('推广人ID(用户ID, 商户ID 或 运营中心ID)');
            $table->tinyInteger('origin_type')->index()->default(0)->comment('推广人类型  1-用户 2-商户 3-运营中心');
            $table->integer('invite_count')->default(0)->comment('当日邀请数量');
            $table->timestamps();

            $table->comment = '邀请用户每日统计表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invite_user_statistics_dailies');
    }
}
