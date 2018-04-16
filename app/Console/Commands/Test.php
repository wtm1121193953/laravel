<?php

namespace App\Console\Commands;

use App\Jobs\SettlementJob;
use App\Modules\Merchant\Merchant;
use App\Modules\Wechat\WechatService;
use App\Support\Lbs;
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
     * @throws \EasyWeChat\Kernel\Exceptions\HttpException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle()
    {
        $app = WechatService::getWechatMiniAppForOper(3);
        $token = $app->access_token->getToken();
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $token['access_token'];
        $client = new Client();
        $response = $client->post($url, [
            'body' => json_encode([
                'scene' => 'id=52',
                'page' => 'pages/product/buynow',
                'width' => 500,
                'auto_color' => true
            ]),
        ]);
        dd($response->getBody()->getContents());

        $response = $app->app_code->getUnlimit('{id:52}', [
            'page' => 'pages/product/buynow',
            'width' => 500,
            'auto_color' => true
        ]);
        $filename = $response->save(storage_path('app/public/miniprogram/app_code'));
        dump(asset('storage/miniprogram/app_code/' . $filename));
        $response = $app->app_code->get('pages/product/buynow?id=52', [
            'width' => 600,
            //...
        ]);
        // 保存小程序码到文件
        $filename = $response->save(storage_path('app/public/miniprogram/app_code'));
        dump(asset('storage/miniprogram/app_code/' . $filename));
        dd();
        SettlementJob::dispatch(1);
        dd();
        dd(Carbon::now()->format('Y-m-d'));
        $end = Carbon::now()->subDay()->endOfDay();
        $start = Carbon::now()->subWeek()->startOfDay();
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->startOfMonth()->addDays(14)->endOfDay();
        $start = Carbon::now()->subMonth()->startOfMonth()->addDays(15);
        $end = Carbon::now()->subMonth()->endOfMonth();
        $start = Carbon::now()->subMonth()->startOfMonth();
        $end = Carbon::now()->subMonth()->endOfMonth();
        $start = Carbon::now()->startOfYear();
        $end = Carbon::now()->startOfYear()->addMonths(5)->endOfMonth();
        $start = Carbon::now()->subYear()->startOfYear()->addMonths(6)->startOfMonth();
        $end = Carbon::now()->subYear()->endOfYear();
        $start = Carbon::now()->subYear()->startOfYear();
        $end = Carbon::now()->subYear()->endOfYear();
        dd($start, $end);
        dd(Carbon::now()->day, Carbon::now()->startOfMonth()->addDays(14)->endOfDay());
        dump(Carbon::now()->subDay()->endOfDay());
        dd(Carbon::now()->subWeek()->startOfDay());
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
