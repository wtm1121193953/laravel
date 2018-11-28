<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->tinyInteger('merchant_type')->default(1)->comment('订单类型 1普通商户订单 2超市商户订单')->after('oper_id');
            $table->tinyInteger('deliver_type')->default(0)->comment('超市订单 发货方式 1-配送 2-自提');
            $table->string('express_company')->default('')->comment('超市订单 快递公司');
            $table->string('express_no')->default('')->comment('超市订单 快递单号');
            $table->string('express_address', 1500)->default('')->comment('超市订单 地址 json');
            $table->dateTime('deliver_time')->nullable()->comment('超市订单 发货时间');
            $table->dateTime('take_delivery_time')->nullable()->comment('超市订单 收货时间');
            $table->dateTime('user_deleted_at')->nullable()->comment('用户删除订单时间');
            $table->decimal('delivery_start_price')->default(0.00)->comment('起送价');
            $table->decimal('delivery_charges')->default(0.00)->comment('配送费');
            $table->tinyInteger('delivery_free_start')->default(0)->comment('是否开启满多少免运费 1是 0否');
            $table->decimal('delivery_free_order_amount')->default(0.00)->comment('订单满多少免运费');
            $table->dateTime('delivery_confirmed')->nullable()->comment('确认收货时间');
            $table->decimal('discount_price')->default(0.00)->comment('优惠金额')->after('pay_type');
            $table->decimal('total_price')->default(0.00)->comment('商品总价格')->after('pay_type');
            $table->decimal('deliver_price')->default(0.00)->comment('超市配送费')->after('pay_type');
            $table->tinyInteger('user_deleted')->default(0)->comment('用户是否已删除订单')->after('deleted_at');
            $table->string('deliver_code')->default('')->comment('超市订单 取货码')->after('deliver_type');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'merchant_type',
                'deliver_type',
                'express_company',
                'express_no',
                'express_address',
                'deliver_time',
                'take_delivery_time',
                'user_deleted_at',
                'delivery_start_price',
                'delivery_charges',
                'delivery_free_start',
                'delivery_free_order_amount',
                'delivery_confirmed',
                'discount_price',
                'total_price',
                'deliver_price',
                'user_deleted',
                'deliver_code'
            ]);
        });
    }
}
