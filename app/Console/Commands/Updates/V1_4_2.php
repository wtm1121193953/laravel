<?php

namespace App\Console\Commands\Updates;

use App\Jobs\OrderFinishedJob;
use App\Modules\Merchant\MerchantService;
use App\Modules\Order\Order;
use App\Modules\Order\OrderRefund;
use App\Modules\UserCredit\UserCreditSettingService;
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
                        $item->save();

                        $bar->advance();
                    }
                });
            });
        $bar->finish();
        $this->info("\n更新现有订单数据中的费率字段 Finished");

        // 历史订单分润
        $this->info('历史订单分润 Start');
        $bar = $this->output->createProgressBar(Order::where('splitting_status', 1)->count('id'));
        Order::chunk(1000, function ($list) use ($bar) {
            $list->each(function (Order $item) use ($bar) {
                if($item->splitting_status == 1){
                    OrderFinishedJob::dispatch($item);
                    $bar->advance();
                }
            });
        });
        $bar->finish();
        $this->info("\n历史订单分润 Finished: 已发放全部任务到队列");


        // 历史订单消费额转换
    }
}
