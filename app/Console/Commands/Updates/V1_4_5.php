<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/9/27/027
 * Time: 11:51
 */
namespace App\Console\Commands\Updates;

use App\Jobs\Schedule\OperStatisticsDailyJob;
use Illuminate\Console\Command;

class V1_4_5 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:v1.4.5';

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
     * @throws \Exception
     */
    public function handle()
    {

        /*************统计运营中心5月份之后历史运营数据start*************/
        $day = 200;

        for($i=1; $i<$day; $i++) {
            $endTime = date('Y-m-d',strtotime("-{$i} day")) . ' 23:59:59';
            OperStatisticsDailyJob::dispatch($endTime);
            if(date('Y-m-d',strtotime("-{$i} day"))<='2018-05-01') {
                break;
            }
        }
        /*************统计运营中心5月份之后历史运营数据end*************/

        dd('ok');
    }
}