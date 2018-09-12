<?php

namespace App\Console\Commands\Updates;

use App\Jobs\OrderFinishedJob;
use App\Jobs\TpsBindsUpdateTpsUidJob;
use App\Modules\Merchant\MerchantService;
use App\Modules\Order\Order;
use App\Modules\Order\OrderRefund;
use App\Modules\Tps\TpsBind;
use App\Modules\UserCredit\UserCreditSettingService;
use App\Modules\Wallet\Bank;
use App\Support\TpsApi;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class V1_4_2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:v1.4.2';

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
        //
        // 初始化分润默认配置
        UserCreditSettingService::set('fee_splitting_ratio_to_self', 5);
        UserCreditSettingService::set('fee_splitting_ratio_to_parent_of_user', 20);
        UserCreditSettingService::set('fee_splitting_ratio_to_parent_of_merchant_level_1', 20);
        UserCreditSettingService::set('fee_splitting_ratio_to_parent_of_merchant_level_2', 25);
        UserCreditSettingService::set('fee_splitting_ratio_to_parent_of_merchant_level_3', 30);
        UserCreditSettingService::set('fee_splitting_ratio_to_parent_of_oper', 20);
        // 补充退款中的退款单号
        OrderRefund::chunk(1000, function(Collection $list){
            $list->each(function (OrderRefund $item){
                $item->refund_no = 'R' . $item->created_at->format('YmdHis') . rand(1000, 9999);
            });
        });

        // 1. 更新现有订单数据中的费率字段
        $this->info('更新现有订单数据中的费率字段 Start');
        $bar = $this->output->createProgressBar(Order::where('settlement_rate', 0)->count('id'));
        Order::chunk(1000, function ($list) use ($bar) {
                $list->each(function (Order $item) use ($bar) {
                    if($item->settlement_rate == 0){
                        $merchant = MerchantService::getById($item->merchant_id, ['id', 'settlement_rate']);
                        $item->settlement_rate = !empty($merchant) ? $merchant->settlement_rate : 0;
                        $item->settlement_charge_amount = $item->price * $item->settlement_rate / 100;
                        $item->settlement_real_amount = $item->price - $item->settlement_charge_amount;
                        $item->save();

                        $bar->advance();
                    }
                });
            });
        $bar->finish();
        $this->info("\n更新现有订单数据中的费率字段 Finished");

        // 历史订单分润
        /*$this->info('历史订单分润 Start');
        $bar = $this->output->createProgressBar(Order::where('status', Order::STATUS_FINISHED)->where('splitting_status', Order::SETTLEMENT_STATUS_NO)->count('id'));
        Order::where('status', Order::STATUS_FINISHED)->chunk(1000, function ($list) use ($bar) {
            $list->each(function (Order $item) use ($bar) {
                if($item->splitting_status == 1){
                    OrderFinishedJob::dispatch($item);
                    $bar->advance();
                }
            });
        });
        $bar->finish();
        $this->info("\n历史订单分润 Finished: 已发放全部任务到队列");*/

        // 历史订单消费额转换
        $this->info("\n初始化银行列表 Start");
        $banks = [
            '中国工商银行',
            '招商银行',
            '中国农业银行',
            '中国建设银行',
            '中国银行',
            '中国民生银行',
            '中国光大银行',
            '中信银行',
            '交通银行',
            '兴业银行',
            '交通银行',
            '中国人民银行',
            '华夏银行',
            '中国邮政储蓄银行'
        ];
        // 清空银行信息
        Bank::where('id', '>', 0)->delete();
        foreach ($banks as $item){
            $bank = new Bank();
            $bank->name = $item;
            $bank->save();
        }
        $this->info("\n初始化银行列表 End");

        //更新旧的tps绑定数据中的tps_uid字段
        $this->info('更新旧的tps绑定数据中的tps_uid字段 Start');
        $bar = $this->output->createProgressBar(TpsBind::where('tps_uid', '=','0')->count('id'));
        TpsBind::chunk(1000, function ($list) use ($bar) {
            $list->each(function ($item) use ($bar) {
                if ($item->tps_uid == 0 ) {
                    TpsBindsUpdateTpsUidJob::dispatch($item);
                    $bar->advance();
                }

            });
        });
        $bar->finish();
        $this->info("\n更新旧的tps绑定数据中的tps_uid字段 Finished");
    }
}
