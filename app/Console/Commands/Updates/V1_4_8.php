<?php

namespace App\Console\Commands\Updates;

use App\Jobs\ImageMigrationToCOSJob;
use App\Jobs\InitMerchantFirstActiveTime;
use App\Jobs\Schedule\OperAndMerchantAndUserStatisticsDailyJob;
use App\Modules\Bizer\BizerIdentityAuditRecord;
use App\Modules\Dishes\DishesGoods;
use App\Modules\Dishes\DishesItem;
use App\Modules\Goods\Goods;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAudit;
use App\Modules\Merchant\MerchantDraft;
use App\Modules\Oper\Oper;
use App\Modules\Order\Order;
use App\Modules\Payment\Payment;
use App\Modules\Settlement\Settlement;
use App\Modules\Settlement\SettlementPlatform;
use App\Modules\User\User;
use App\Modules\User\UserIdentityAuditRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class V1_4_8 extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'update:v1.4.8';

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
            "ALTER TABLE `oper_statistics`
	CHANGE COLUMN `merchant_num` `merchant_num` INT(11) NOT NULL DEFAULT '0' COMMENT '商户数(正式)' AFTER `oper_id`",
            "ALTER TABLE `oper_statistics`
	CHANGE COLUMN `order_paid_num` `order_paid_num` INT(11) NOT NULL DEFAULT '0' COMMENT '总订单量（已完成）' AFTER `oper_and_merchant_invite_num`,
	CHANGE COLUMN `order_paid_amount` `order_paid_amount` DECIMAL(11,2) NOT NULL DEFAULT '0.00' COMMENT '总订单金额（已完成）' AFTER `order_refund_num`",
            "ALTER TABLE `merchants`
	CHANGE COLUMN `active_time` `active_time` DATETIME NULL DEFAULT NULL COMMENT '最近激活时间, 即商户最近一次审核通过时间' AFTER `oper_biz_member_code`"
        ];
        foreach ($sqls as $sql) {
            DB::statement($sql);
        }
        $this->info('备注修改完成');

        // 初始化支付方式
        Payment::where('id', '>', 0)->delete();
        $sqls = [
            "INSERT INTO `payments` (`id`, `type`, `name`, `view_name`, `logo_url`, `class_name`, `status`, `on_pc`, `on_miniprogram`, `on_app`, `configs`, `created_at`, `updated_at`) VALUES (1, 1, '微信', '微信', 'https://daqian-public-1257640953.cos.ap-guangzhou.myqcloud.com/1b21d7cc263fb6e503c4dd8e054d3e67.png', 'WechatPay', 1, 1, 1, 1, '{\"a\":\"1\",\"b\":\"22\"}', '2018-10-15 16:04:27', '2018-11-12 15:19:19')",
            "INSERT INTO `payments` (`id`, `type`, `name`, `view_name`, `logo_url`, `class_name`, `status`, `on_pc`, `on_miniprogram`, `on_app`, `configs`, `created_at`, `updated_at`) VALUES (2, 2, '支付宝支付', '', 'https://daqian-public-1257640953.cos.ap-guangzhou.myqcloud.com/1ed794dd63cd8fcc5f26882680844837.jpg', 'AliPay', 1, 0, 0, 0, '{\"a\":\"1\",\"b\":\"22\"}', '2018-10-16 10:16:37', '2018-10-16 11:15:58')",
            "INSERT INTO `payments` (`id`, `type`, `name`, `view_name`, `logo_url`, `class_name`, `status`, `on_pc`, `on_miniprogram`, `on_app`, `configs`, `created_at`, `updated_at`) VALUES (3, 1, '融宝支付', '', 'https://daqian-public-1257640953.cos.ap-guangzhou.myqcloud.com/1ed794dd63cd8fcc5f26882680844837.jpg', 'ReaPay', 1, 0, 0, 0, '', '2018-10-16 10:20:56', '2018-10-16 10:20:56')",
            "INSERT INTO `payments` (`id`, `type`, `name`, `view_name`, `logo_url`, `class_name`, `status`, `on_pc`, `on_miniprogram`, `on_app`, `configs`, `created_at`, `updated_at`) VALUES (4, 0, '钱包余额', '钱包余额', 'https://daqian-public-1257640953.cos.ap-guangzhou.myqcloud.com/bba04c0fd4c57de789aa180372ca65b0.png', 'WalletPay', 1, 0, 1, 1, '', '2018-10-29 14:04:17', '2018-11-12 15:17:55')",
        ];
        foreach ($sqls as $sql) {
            DB::statement($sql);
        }
        $this->info('初始化支付方式');

        //填充商户首次审核通过时间
        $this->info('开始填充商户首次审核通过时间');
        Merchant::chunk(50, function ($merchants) {
            foreach ($merchants as $merchant) {
                InitMerchantFirstActiveTime::dispatch($merchant);
            }
        });
        $this->info('填充商户首次审核通过时间完成');

        /*************统计运营中心5月份之后历史运营数据start*************/
        $this->info('统计运营中心5月份之后历史运营数据start');
        $i = 1;
        while (1) {
            $endTime = date('Y-m-d', strtotime("-{$i} day")) . ' 23:59:59';
            OperAndMerchantAndUserStatisticsDailyJob::dispatch($endTime);
            if (date('Y-m-d', strtotime("-{$i} day")) <= '2018-04-17') {
                break;
            }
            $i++;
        }
        $this->info('统计运营中心5月份之后历史运营数据end');

        /**********************系统图片迁移COS start**********************/
        $this->info('系统图片迁移COS start');
        // 待修改字段
        $changModel = [
            DishesGoods::class => ['detail_image'],
            DishesItem::class => ['dishes_goods_detail_image'],
            Goods::class => [
                'thumb_url',
                'pic',
                'pic_list'
            ],
            Merchant::class => ['logo'
                , 'desc_pic'
                , 'desc_pic_list'
                , 'business_licence_pic_url'
                , 'tax_cert_pic_url'
                , 'legal_id_card_pic_a'
                , 'legal_id_card_pic_b'
                , 'contract_pic_url'
                , 'licence_pic_url'
                , 'hygienic_licence_pic_url'
                , 'agreement_pic_url'
                , 'bank_card_pic_a'
                , 'other_card_pic_urls'
            ],
            Oper::class => [
                'licence_pic_url',
                'business_licence_pic_url'
            ],
            Order::class => [
                'goods_pic',
                'goods_thumb_url'
            ],
            Settlement::class => [
                'pay_pic_url',
                'invoice_pic_url'
            ],
            BizerIdentityAuditRecord::class => [
                'front_pic',
                'opposite_pic'
            ],
            MerchantDraft::class => [
                'logo',
                'desc_pic',
                'desc_pic_list',
                'business_licence_pic_url',
                'tax_cert_pic_url',
                'legal_id_card_pic_a',
                'legal_id_card_pic_b',
                'contract_pic_url',
                'licence_pic_url',
                'hygienic_licence_pic_url',
                'agreement_pic_url',
                'bank_card_pic_a',
                'other_card_pic_urls'
            ],
            SettlementPlatform::class => [
                'pay_pic_url',
                'invoice_pic_url'
            ],
            UserIdentityAuditRecord::class => [
                'front_pic',
                'opposite_pic'
            ],
            User::class => [
                'avatar_url'
            ]
        ];
        $count = 0;
        foreach ($changModel as $modelName => $v) {
            $count += $modelName::count();
        }

        $bar = $this->output->createProgressBar($count);
        foreach ($changModel as $modelName => $columns) {
            $searchColumn = $columns;
            array_push($searchColumn,'id');
            $modelName::select($searchColumn)
                ->chunk(1000, function ( $list ) use ( $columns, $bar ) {
                $list->each(function ( $data ) use ( $columns, $bar ) {
                    ImageMigrationToCOSJob::dispatch($data, $columns);
                    $bar->advance();
                });
                unset($list);
            });
        }
        $bar->finish();
        $this->info('系统图片迁移COS end');
        /**********************系统图片迁移COS end**********************/


        /**********************结算单历史数据 start**********************/
        $this->info('结算单历史数据 start');
        $sql = "update settlement_platforms s set settlement_cycle_type = (select settlement_cycle_type from merchants where id = s.merchant_id);";
        DB::statement($sql);
        $this->info('结算单历史数据 end');
        /**********************结算单历史数据 end**********************/
    }
}