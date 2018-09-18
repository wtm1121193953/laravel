<?php

namespace App\Jobs;

use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Wechat\MiniprogramScene;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserInviteChannelsDataMigration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $originId;
    protected $originType;

    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct($originId, $originType)
    {
        $this->originId = $originId;
        $this->originType = $originType;
    }

    /**
     * Execute the job.
     * @return void
     */
    public function handle()
    {
        try {
            // 获取需要解绑的旧用户渠道
            $oldChannelsIds = InviteChannel::where('origin_id', $this->originId)
                ->where('origin_type', $this->originType)
                ->pluck('id')
                ->toArray();
            $oldChannelsIdsStr = implode(",", $oldChannelsIds);
            DB::beginTransaction();
            try {
                // 判断用户是否有平台的渠道
                $inviteChannel = InviteChannel::where('oper_id', 0)
                    ->where('origin_id', $this->originId)
                    ->where('origin_type', $this->originType)
                    ->first();
                if(empty($inviteChannel)){
                    $inviteChannel = new InviteChannel();
                    $inviteChannel->oper_id = 0;
                    $inviteChannel->origin_id = $this->originId;
                    $inviteChannel->origin_type = $this->originType;
                    $inviteChannel->remark = 'new |' . $oldChannelsIdsStr;
                    $inviteChannel->save();
                }
                // 新生成的渠道ID
                $newChannelId = $inviteChannel->id;
                // 添加解绑标记
                InviteChannel::whereIn('id', $oldChannelsIds)
                    ->update(['remark' => 'unbind']);

                // 更新场景值的invite_channel_id为新的渠道ID
                MiniprogramScene::whereIn('invite_channel_id', $oldChannelsIds)
                    ->update(['invite_channel_id' => $newChannelId]);

                // 更新邀请记录的invite_channel_id为新的渠道ID
                InviteUserRecord::whereIn('invite_channel_id', $oldChannelsIds)
                    ->update(['invite_channel_id' => $newChannelId]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('解绑用户渠道任务出错, 错误原因:' . $e->getMessage(), [
                'originId' => $this->originId,
                'originType' => $this->originType,
                'timestamp' => date('Y-m-d H:i:s'),
                'exception' => $e,
            ]);
        }
    }
}
