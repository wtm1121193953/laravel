<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperBizMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oper_biz_members', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oper_id')->index()->default(0)->comment('运营中心ID');
            $table->string('name', 50)->default('')->comment('姓名');
            $table->string('mobile', 15)->default('')->comment('手机号');
            $table->string('code', 10)->index()->default('')->comment('推荐码 (唯一性由程序去限制)');
            $table->string('remark')->default('')->comment('备注');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-正常 2-冻结');
            $table->timestamps();

            $table->comment = '运营中心业务员信息表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oper_biz_members');
    }
}
