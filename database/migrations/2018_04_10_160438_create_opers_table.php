<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('')->comment('运营中心名称');
            $table->tinyInteger('status')->index()->default(1)->comment('状态 1-正常合作中 2-已冻结 3-停止合作');

            // 运营中心资料
            $table->string('contacter')->default('')->comment('联系人');
            $table->string('tel')->default('')->comment('联系电话');
            $table->string('province')->default('')->comment('所在省份');
            $table->integer('province_id')->default(0)->comment('所在省份Id');
            $table->string('city')->default('')->comment('所在城市');
            $table->integer('city_id')->default(0)->comment('所在城市Id');
            $table->string('area')->default('')->comment('所在县区');
            $table->integer('area_id')->default(0)->comment('所在县区Id');
            $table->string('address')->default('')->comment('详细地址');
            $table->string('email')->default('')->comment('邮箱');
            $table->string('legal_name')->default('')->comment('法人姓名');
            $table->string('legal_id_card')->default('')->comment('法人身份证号');
            $table->tinyInteger('invoice_type')->default(0)->comment('发票类型 0-其他 1-增值税普票 2-增值税专票 3-国税普票');
            $table->decimal('invoice_tax_rate', 4, 2)->default(0)->comment('发票税点');
            $table->tinyInteger('settlement_cycle_type')->default(1)->comment('结款周期 1-周结 2-半月结 3-月结 4-半年结 5-年结 [保留字段, 暂时无用]');
            $table->string('bank_card_no')->default('')->comment('公司银行账号');
            $table->string('sub_bank_name')->default('')->comment('开户支行名称');
            $table->string('bank_open_name')->default('')->comment('开户名');
            $table->string('bank_open_address')->default('')->comment('开户地址');
            $table->string('bank_code')->default('')->comment('银行代码');
            $table->string('licence_pic_url')->default('')->comment('开户许可证');
            $table->string('business_licence_pic_url')->default('')->comment('营业执照');

            $table->timestamps();
            $table->softDeletes();

            $table->comment = '运营中心表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opers');
    }
}
