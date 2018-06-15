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

class UserLevelCalculationJob implements ShouldQueue
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
        $userTotalCredit = UserCredit::where('user_id', $this->originId)->value('total_credit');
        $userLevel = UserCreditSettingService::getUserLevelByCreditNumber($userTotalCredit);
        $user = User::findOrFail($this->originId);
        if ($user->level != $userLevel){
            $user->level = $userLevel;
            $user->save();
        }
    }
}
