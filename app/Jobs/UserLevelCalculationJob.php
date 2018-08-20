<?php

namespace App\Jobs;

use App\Modules\User\User;
use App\Modules\User\UserLevelChangeRecords;
use App\Modules\UserCredit\UserCredit;
use App\Modules\UserCredit\UserCreditSettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * 用户积分已经去掉了
 * Class UserLevelCalculationJob
 * @package App\Jobs
 * @deprecated
 */
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
            // 添加用户等级改变的记录数据
            $userLevelChangeRecord = new UserLevelChangeRecords();
            $userLevelChangeRecord->user_id = $this->originId;
            $userLevelChangeRecord->prev_user_level = $user->level;
            $userLevelChangeRecord->next_user_level = $userLevel;
            $userLevelChangeRecord->save();

            // 修改用户表的用户等级
            $user->level = $userLevel;
            $user->save();
        }
    }
}
