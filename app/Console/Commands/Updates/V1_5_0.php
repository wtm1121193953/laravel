<?php

namespace App\Console\Commands\Updates;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class V1_5_0 extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'update:v1.5.0';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $sqls = [
            "ALTER TABLE `orders`
	CHANGE COLUMN `status` `status` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '状态 1-未支付 2-已取消 3-已关闭 (超时自动关闭) 4-已支付 5-退款中[保留状态] 6-已退款 7-已完成 (不可退款) 8-待发货 9-待自提 10-已发货' AFTER `buy_number`",
            "ALTER TABLE `orders`
	CHANGE COLUMN `type` `type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '订单类型 1-团购订单 2-扫码支付订单 3-点菜订单 4-超市订单' AFTER `merchant_name`",
            "ALTER TABLE `fee_splitting_records`
	CHANGE COLUMN `origin_type` `origin_type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '分润用户类型, 1-用户 2-商户 3-运营中心 4-业务员 5-超市' AFTER `origin_id`",
            "ALTER TABLE `wallets`
	CHANGE COLUMN `origin_type` `origin_type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '用户类型 1-用户 2-商户 3-运营中心 4-业务员 5-超市' AFTER `origin_id`",
            "ALTER TABLE `wallet_balance_unfreeze_records`
	CHANGE COLUMN `origin_type` `origin_type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '用户类型 1-用户 2-商户 3-运营中心  4-业务员 5-超市' AFTER `origin_id`",
            "ALTER TABLE `wallet_bills`
	CHANGE COLUMN `origin_type` `origin_type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '用户类型 1-用户 2-商户 3-运营中心 4-业务员 5-超市' AFTER `origin_id`",
            "ALTER TABLE `wallet_consume_quota_records`
	CHANGE COLUMN `origin_type` `origin_type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '用户类型 1-用户 2-商户 3-运营中心 4-业务员 5-超市' AFTER `origin_id`",
            "ALTER TABLE `wallet_consume_quota_unfreeze_records`
	CHANGE COLUMN `origin_type` `origin_type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '用户类型 1-用户 2-商户 3-运营中心 4-业务员 5-超市' AFTER `origin_id`",
            "ALTER TABLE `wallet_withdraws`
	CHANGE COLUMN `origin_type` `origin_type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '用户类型 1-用户 2-商户 3-运营中心 4-业务员 5-超市' AFTER `origin_id`",
            "ALTER TABLE `invite_user_records`
	CHANGE COLUMN `origin_type` `origin_type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '推广人类型  1-用户 2-商户 3-运营中心 5-超市' AFTER `origin_id`",
            "ALTER TABLE `invite_user_statistics_dailies`
	CHANGE COLUMN `origin_type` `origin_type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '推广人类型  1-用户 2-商户 3-运营中心 5-超市' AFTER `origin_id`",
            "ALTER TABLE `wallet_bills`
	CHANGE COLUMN `type` `type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '类型  1-个人消费返利  2-下级消费返利  3-个人消费返利退款  4-下级消费返利退款 5-运营中心交易分润 6-运营中心交易分润退款 7-提现 8-提现失败 9-业务员交易分润 10-业务员交易分润退款 11-平台购物 12-平台退款' AFTER `bill_no`",
        ];
        foreach ($sqls as $sql) {
            DB::statement($sql);
        }
        $this->info('备注修改完成');

    }
}