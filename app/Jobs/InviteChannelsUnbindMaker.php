<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Invite\InviteChannel;
use Illuminate\Support\Facades\Log;
use App\Jobs\InviteChannelsUnbindCustomer;

/**
 * 批量更改渠道信息队列生产者
 * Class InviteChannelsUnbindMaker
 * Author:   JerryChan
 * Date:     2018/9/14 11:43
 * @package App\Jobs
 */
class InviteChannelsUnbindMaker implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        Log::info('开始渠道生成更新任务');
        InviteChannel::where('origin_type', InviteChannel::ORIGIN_TYPE_USER)
            ->where('remark','')
            ->select(['origin_id', 'origin_type'])
            ->groupBy(['origin_id', 'origin_type'])
            ->chunk(1000, function ( $channels ) {
                $channels->each(function ( $channel ) {
                    InviteChannelsUnbindCustomer::dispatch( $channel->origin_id, $channel->origin_type );
                });
            });
        Log::info('渠道更新任务生成完成');
    }
}
