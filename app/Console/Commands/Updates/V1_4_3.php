<?php

namespace App\Console\Commands\Updates;

use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Order\Order;
use Illuminate\Console\Command;

class V1_4_3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:v1.4.3';

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
        // 1. 更新现有订单数据中的费率字段
        $this->info('更新现有订单数据中的费率字段 Start');
        $bar = $this->output->createProgressBar(Order::where('settlement_rate', 0)->count('id'));
        Order::where('settlement_rate', 0)
            ->chunk(1000, function ($list) use ($bar) {
                $list->each(function (Order $item) use ($bar) {
                    $item->settlement_rate = MerchantService::getById($item->merchant_id, ['id', 'settlement_rate'])->settlement_rate;
                    $item->save();

                    $bar->advance();
                });
            });
        $bar->finish();
        $this->info("\n更新现有订单数据中的费率字段 Finished");
    }
}
