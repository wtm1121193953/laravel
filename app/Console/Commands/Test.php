<?php

namespace App\Console\Commands;

use App\Jobs\SettlementJob;
use App\Modules\Merchant\Merchant;
use App\Modules\Wechat\WechatService;
use App\Support\Lbs;
use Faker\Factory;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

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
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle()
    {
        SettlementJob::dispatch(Merchant::SETTLE_WEEKLY);
        SettlementJob::dispatch(Merchant::SETTLE_HALF_MONTHLY);
        SettlementJob::dispatch(Merchant::SETTLE_MONTHLY);
        SettlementJob::dispatch(Merchant::SETTLE_HALF_YEARLY);
        SettlementJob::dispatch(Merchant::SETTLE_YEARLY);
        dd();
        dd(new \Illuminate\Support\Carbon(Factory::create()->dateTimeBetween('-7 days')->format('Y-m-d H:i:s')));
        dd(Factory::create()->dateTimeBetween('-7 days')->format('YmdHis'));
        SettlementJob::dispatch(1);
        dd();
//        Redis::geoadd('location:merchant', 113.99531, 22.709883, 1);
//        Redis::geoadd('location:merchant', 114.002176, 22.663525, 2);
//        Redis::geoadd('location:merchant', 114.002176, 22.664525, 3);

//        dump(Redis::geodist('location:merchant', 1, 5, 'km'));
//        dump($result = Redis::georadius('location:merchant', 114.002176, 22.664525, 6, 'km', 'WITHDIST', 'asc', 'count',  2));
//        dump(Lbs::getNearlyMerchantDistanceByGps(114.101585,22.471113, 200000));
//        dump(Merchant::all()->sortByDesc('id')->forPage(2, 3)->values());
        //
//        preg_match('/servicewechat.com\/(wx[\d0-9a-zA-Z]*)\/.*/', 'https://servicewechat.com/wx1abb4cf60ffea6c9/devtools/page-frame.html', $matches);
//        dump($matches);
//        $app = WechatService::getWechatMiniAppForOper(1);
//        $result = $app->auth->session('');
//        dump($result);
    }
}
