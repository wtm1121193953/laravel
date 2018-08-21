<?php

namespace App\Jobs;

use App\Exceptions\DataNotFoundException;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Invite\InviteUserService;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\Oper;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Modules\Setting\SettingService;
use App\Modules\User\User;
use App\Modules\User\UserService;
use App\Modules\UserCredit\UserConsumeQuotaRecord;
use App\Modules\UserCredit\UserCredit;
use App\Modules\UserCredit\UserCreditRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderFinishedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        //
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        // 1. 执行分润
        $this->feeSplitting();
        // 2. 处理消费额 消费额逻辑暂时去掉, 需要修改
        $this->consumeQuota();
        // 延迟24小时分发解冻积分以及消费额操作
//        FeeSplittingUnfreezeJob::dispatch()->delay();
//        ConsumeQuotaUnfreezeJob::dispatch()->delay();
    }

    /**
     * 处理分润
     */
    private function feeSplitting()
    {
        FeeSplittingService::feeSplittingByOrder($this->order);
    }

    /**
     * 处理消费额
     */
    private function consumeQuota()
    {

    }

}
