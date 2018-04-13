<?php

namespace App\Console\Commands;

use App\Modules\Merchant\Merchant;
use App\Support\Lbs;
use Illuminate\Console\Command;
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
     */
    public function handle()
    {
//        Redis::geoadd('location:merchant', 113.99531, 22.709883, 1);
//        Redis::geoadd('location:merchant', 114.002176, 22.663525, 2);
//        Redis::geoadd('location:merchant', 114.002176, 22.664525, 3);

//        dump(Redis::geodist('location:merchant', 1, 5, 'km'));
//        dump($result = Redis::georadius('location:merchant', 114.002176, 22.664525, 6, 'km', 'WITHDIST', 'asc', 'count',  2));
        dump(Lbs::getNearlyMerchantDistanceByGps(114.101585,22.471113, 200000));
//        dump(Merchant::all()->sortByDesc('id')->forPage(2, 3)->values());
        //
    }
}
