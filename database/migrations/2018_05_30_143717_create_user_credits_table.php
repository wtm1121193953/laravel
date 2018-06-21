<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_credits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index()->default(0)->comment('用户ID');
            $table->integer('total_credit')->default(0)->comment('累计积分');
            $table->integer('used_credit')->default(0)->comment('已使用积分(可用积分显示动态计算,累计积分-已使用积分=可用积分)');
            $table->decimal('consume_quota', 12, 2)->default(0)->comment('累计消费额(累计额度是独立于积分的另一套算法, = 用户消费额度(元) + 直推用户消费额度 * 系数[如50%])');
            $table->timestamps();
            $table->comment = '用户积分总表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_credits');
    }
}
