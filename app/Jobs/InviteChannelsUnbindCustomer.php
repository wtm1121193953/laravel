<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Invite\InviteChannelService;
use Illuminate\Support\Facades\Log;

class InviteChannelsUnbindCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $originId;
    protected $originType;

    /**
     * Create a new job instance.
     * @return void
     */
    public function __construct( $originId, $originType )
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
            InviteChannelService::unbindChannels($this->originId, $this->originType);
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
