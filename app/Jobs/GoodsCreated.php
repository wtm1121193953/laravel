<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * 商品创建成功后的操作
 * Class GoodsCreated
 * @package App\Jobs
 */
class GoodsCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $goods;

    /**
     * Create a new job instance.
     *
     * @param $goods
     */
    public function __construct($goods)
    {
        //
        $this->goods = $goods;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 添加全文搜索索引
    }
}
