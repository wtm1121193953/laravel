<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/9/27/027
 * Time: 11:51
 */

namespace App\Console\Commands\Updates;

use App\Jobs\ImageMigrationToCOSJob;
use App\Jobs\Schedule\OperAndMerchantAndUserStatisticsDailyJob;
use App\Modules\Bizer\BizerIdentityAuditRecord;
use App\Modules\Dishes\DishesGoods;
use App\Modules\Dishes\DishesItem;
use App\Modules\Goods\Goods;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantDraft;
use App\Modules\Oper\Oper;
use App\Modules\Order\Order;
use App\Modules\Settlement\Settlement;
use App\Modules\Settlement\SettlementPlatform;
use App\Modules\User\User;
use App\Modules\User\UserIdentityAuditRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class V1_4_5 extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'update:v1.4.5';

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

        /*************统计运营中心5月份之后历史运营数据start*************/
        $i = 1;
        while (1) {
            $endTime = date('Y-m-d', strtotime("-{$i} day")) . ' 23:59:59';
            OperAndMerchantAndUserStatisticsDailyJob::dispatch($endTime);
            if (date('Y-m-d', strtotime("-{$i} day")) <= '2018-04-17') {
                break;
            }
            $i++;
        }
        /*************统计运营中心5月份之后历史运营数据end*************/
        /**********************系统图片迁移COS start**********************/
        // 待修改字段
        /*$changModel = [
            DishesGoods::class => ['detail_image'],
            DishesItem::class => ['dishes_goods_detail_image'],
            Goods::class => [
                'thumb_url',
                'pic',
                'pic_list' => ','
            ],
            Merchant::class => ['logo'
                , 'desc_pic'
                , 'desc_pic_list' => ','
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
                'desc_pic_list' => ',',
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
            $modelName::chunk(1000, function ( $list ) use ( $columns, $bar ) {
                $list->each(function ( $data ) use ( $columns, $bar ) {
                    ImageMigrationToCOSJob::dispatch($data, $columns);
                    $bar->advance();
                });
                unset($list);
            });
        }
        $bar->finish();*/
        /**********************系统图片迁移COS end**********************/

        // 修改商户法人银行卡图片字段长度
        /*$sql = 'ALTER TABLE `merchants`
	CHANGE COLUMN `bank_card_pic_a` `bank_card_pic_a` VARCHAR(500) NOT NULL DEFAULT \'\' COMMENT \'法人银行卡正面照\' COLLATE \'utf8mb4_unicode_ci\' AFTER `service_phone`';
        DB::update($sql);
        dd('ok');*/
    }
}