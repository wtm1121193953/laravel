<?php

namespace App\Console\Commands;

use App\Modules\Dishes\DishesGoods;
use App\Modules\Goods\GoodsService;
use Illuminate\Console\Command;

class InitGoodsAndDishesGoodsSort extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'goods_and_dishes_goods:sort';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化goods与dishes_goods的[sort]数据，代码上线时只执行一次';

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
        GoodsService::initSortData();
        DishesGoods::initSortData();
        echo 'OK';
    }
}
