<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperBizersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oper_bizers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oper_id')->index()->comment('运营中心ID');
            $table->integer('bizer_id')->index()->comment('业务员ID');
            $table->dateTime('sign_time')->nullable()->comment('签约时间');
            $table->tinyInteger('sign_status')->default(1)->comment('签约状态，1-正常，0-冻结');
            $table->decimal('divide', 10, 2)->default(0)->comment('分成比例');
            $table->string('remark', 512)->default('')->comment('申请备注');
            $table->string('note', 512)->default('')->comment('签约或拒绝原因');
            $table->tinyInteger('status')->default(0)->comment('状态 0申请中, 1-签约 -1-拒绝');
            $table->tinyInteger('is_tips')->default(0)->comment('是否提示，1-已提示，0-未提示');
            $table->timestamps();
            $table->comment = '运营中心业务员关联表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oper_bizers');
    }
}
