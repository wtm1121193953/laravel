<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperMiniprogramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oper_miniprograms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('oper_id')->index()->default(0)->comment('用户所属运营中心ID');
            $table->string('name')->default('')->comment('小程序名称');
            $table->string('appid')->default('')->comment('小程序appid');
            $table->string('secret')->default('')->comment('小程序secret');
            $table->string('verify_file_path')->default('')->comment('业务域名验证文件路径, 示例值: "/F2aDABHkQT.txt"');
            $table->string('mch_id')->default('')->comment('微信支付商户号');
            $table->string('key')->default('')->comment('微信支付密钥');
            $table->string('cert_zip_path')->default('')->comment('支付证书zip路径');
            $table->timestamps();

            $table->comment = '运营中心小程序配置表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oper_miniprograms');
    }
}
