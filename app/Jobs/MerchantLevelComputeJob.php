<?php

namespace App\Jobs;

use App\Modules\Invite\InviteUserRecord;
use App\Modules\Merchant\Merchant;
use App\Modules\UserCredit\UserCreditSettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class MerchantLevelComputeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $merchantId;

    /**
     * Create a new job instance.
     *
     * @param $merchantId
     */
    public function __construct($merchantId)
    {
        //
        $this->merchantId = $merchantId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $inviteCount = InviteUserRecord::where('origin_id', $this->merchantId)
            ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_MERCHANT)
            ->count();
        $merchantLevel = UserCreditSettingService::getMerchantLevelByInviteUserNumber($inviteCount);
        $merchant = Merchant::findOrFail($this->merchantId);
        $merchant->level = $merchantLevel;
        $merchant->save();
    }
}
