<?php

namespace App\Listeners;

use App\Events\MerchantCreatedEvent;
use App\Modules\Oper\OperStatistics;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * 运营中心营销统计，新增商户
 * Class OperStatisticsAddMerchantListener
 * Author:   JerryChan
 * Date:     2018/9/20 16:41
 * @package App\Listeners
 */
class OperStatisticsAddMerchantListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MerchantCreatedEvent  $event
     * @return void
     */
    public function handle(MerchantCreatedEvent $event)
    {
        if(!$event->merchant->id){
            return;
        }
        OperStatistics::where('oper_id', $event->merchant->oper_id)
            ->where('date', $event->date)
            ->increment('merchant_num');
    }
}
