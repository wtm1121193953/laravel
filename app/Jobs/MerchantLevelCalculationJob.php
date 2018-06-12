<?php

namespace App\Jobs;

use App\Modules\Invite\InviteUserRecord;
use App\Modules\Merchant\Merchant;
use App\Modules\User\User;
use App\Modules\UserCredit\UserCredit;
use App\Modules\UserCredit\UserCreditSettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class MerchantLevelCalculationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $originId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($originId)
    {
        //
        $this->originId = $originId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $inviteCount = InviteUserRecord::where('origin_id', $this->originId)
            ->where('origin_type', InviteUserRecord::ORIGIN_TYPE_MERCHANT)
            ->count();
        $merchantLevel = UserCreditSettingService::getMerchantLevelByInviteUserNumber($inviteCount);
        $merchant = Merchant::findOrFail($this->originId);
        $merchant->level = $merchantLevel;
        $merchant->save();
    }
}
