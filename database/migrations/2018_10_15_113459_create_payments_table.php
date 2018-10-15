<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('type')->default(0)->comment('支付方式类型：1微信支付 2支付宝支付...');
            $table->string('name')->default('')->comment('支付方式名称');
            $table->string('logo_url')->default('')->comment('logo地址');
            $table->string('class_name')->default('')->comment('对应类名');
            $table->tinyInteger('status')->default(0)->comment('状态1启用 2禁用');
            $table->tinyInteger('on_pc')->default(0)->comment('是否pc端1是0否');
            $table->tinyInteger('on_miniprogram')->default(0)->comment('是否小程序端1是0否');
            $table->tinyInteger('on_app')->default(0)->comment('是否app端 1是0否');
            $table->text('configs')->comment('配置信息');
            $table->timestamps();
            $table->comment = '支付方式';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
