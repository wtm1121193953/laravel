<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        /*'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],*/
        // Author:Jerry Date:180903
        'App\Events\TpsBindSave'    =>  [
            'App\Listeners\TpsBindSaveEventListener'
        ],

        // 新增 运营中心营销统计 事件监听
        // 添加用户数据统计
        /*'App\Events\InviteUserRecordsCreatedEvent'  =>  [
            'App\Listeners\OperStatisticsAddUserListener'
        ],*/
        // 添加订单记录
        'App\Events\OrdersUpdatedEvent'    =>  [
            'App\Listeners\OperStatisticsAddOrderListener'
        ],
        // 添加商户数
        'App\Events\MerchantCreatedEvent' =>  [
            'App\Listeners\OperStatisticsAddMerchantListener'
        ],
        // 用户注册通知消息
        'App\Events\InviteUserRecordCreatedEvent'  =>  [
            'App\Listeners\MessageNoticeSendListener'
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
