<?php

namespace App\Console\Commands\Updates;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class V1_4_6 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:v1.4.6';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*$sqls = [
            "ALTER TABLE `wallets` CHANGE COLUMN `origin_type` `origin_type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '用户类型 1-用户 2-商户 3-运营中心 4-业务员' AFTER `origin_id`",
            "ALTER TABLE `fee_splitting_records`
    CHANGE COLUMN `origin_type` `origin_type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '分润用户类型, 1-用户 2-商户 3-运营中心 4-业务员' AFTER `origin_id`",
            "ALTER TABLE `fee_splitting_records`
    CHANGE COLUMN `origin_id` `origin_id` INT(11) NOT NULL COMMENT '分润用户ID, 用户ID/商户ID/或运营中心ID/业务员ID' AFTER `id`",
            "ALTER TABLE `fee_splitting_records`
    CHANGE COLUMN `type` `type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '分润类型 1-自返, 2-返上级 3-运营中心交易分润 4-业务员分润' AFTER `amount`",
            "ALTER TABLE `wallet_bills`
    CHANGE COLUMN `origin_type` `origin_type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '用户类型 1-用户 2-商户 3-运营中心 4-业务员' AFTER `origin_id`",
            "ALTER TABLE `wallet_bills`
    CHANGE COLUMN `type` `type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '类型  1-个人消费返利  2-下级消费返利  3-个人消费返利退款  4-下级消费返利退款 5-运营中心交易分润 6-运营中心交易分润退款 7-提现 8-提现失败 9-业务员交易分润 10-业务员交易分润退款' AFTER `bill_no`",
            "ALTER TABLE `wallet_withdraws`
    CHANGE COLUMN `origin_type` `origin_type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '用户类型 1-用户 2-商户 3-运营中心 4-业务员' AFTER `origin_id`",
            "ALTER TABLE `bank_cards`
    CHANGE COLUMN `origin_type` `origin_type` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '分润用户类型, 1-用户 2-商户 3-运营中心 4-业务员' AFTER `origin_id`",
            "INSERT INTO `countries` (`id`, `abbreviation`, `name_en`, `name_zh`, `code`, `flag_icon`, `created_at`, `updated_at`) VALUES (1, 'CN', 'China', '中国', '86', 'https://daqian-public-1257640953.cos.ap-guangzhou.myqcloud.com/a82ff00f39eff54062328b4474c33dbc.png', '2018-10-11 17:15:37', '2018-10-11 17:15:45')",
            "INSERT INTO `countries` (`id`, `abbreviation`, `name_en`, `name_zh`, `code`, `flag_icon`, `created_at`, `updated_at`) VALUES (2, 'HK', 'Hong Kong', '中国香港', '852', 'https://daqian-public-1257640953.cos.ap-guangzhou.myqcloud.com/389d0451c5c2ff40e88a93588dcbd6f1.png', '2018-10-11 17:15:39', '2018-10-11 17:15:47')",
            "INSERT INTO `countries` (`id`, `abbreviation`, `name_en`, `name_zh`, `code`, `flag_icon`, `created_at`, `updated_at`) VALUES (3, 'MO', 'Macau', '中国澳门', '853', 'https://daqian-public-1257640953.cos.ap-guangzhou.myqcloud.com/6339bdf0e24c871301d1fc0207e2685f.png', '2018-10-11 17:15:40', '2018-10-11 17:15:48')",
            "INSERT INTO `countries` (`id`, `abbreviation`, `name_en`, `name_zh`, `code`, `flag_icon`, `created_at`, `updated_at`) VALUES (4, 'TW', 'Taiwan', '中国台湾', '886', 'https://daqian-public-1257640953.cos.ap-guangzhou.myqcloud.com/0e41af2b3ca03d145e7665d0821931fa.png', '2018-10-11 17:15:42', '2018-10-11 17:15:49')",
            "update areas set parent_id = 820000, path = 3 where parent_id = 82",
            "update areas set parent_id = 810000, path = 3 where parent_id = 81",
            "INSERT INTO areas (area_id,name,path,area_code,spell,letter,first_letter,parent_id)
    values (820000, '澳门特别行政区', 2, '1853', 'AOMENTEBIEXINGZHENGQU', 'AMTBXZQ', 'A', 82),
 (810000, '香港特别行政区', 2, '1852', 'XIANGGANGTEBIEXINGZHENGQU', 'XGTBXZQ', 'X', 81)",
        ];
        foreach ($sqls as $sql) {
            DB::statement($sql);
        }
        $this->info('执行成功');*/

        /*$this->info('清理场景码缓存');
        DB::update("UPDATE miniprogram_scenes SET qrcode_url = ''");
        $this->info('清理场景码缓存完成');*/

        $this->info('修复国别数据');
        DB::update("UPDATE countries SET id = 1 WHERE `name_zh` = '中国'");
        DB::update("UPDATE countries SET id = 2 WHERE `name_zh` = '中国香港'");
        DB::update("UPDATE countries SET id = 3 WHERE `name_zh` = '中国澳门'");
        DB::update("UPDATE countries SET id = 4 WHERE `name_zh` = '中国台湾'");
        $this->info('修复国别数据完成');
    }
}
