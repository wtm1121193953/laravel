<?php

namespace App\Jobs;

use App\Exceptions\DataNotFoundException;
use App\Modules\Cs\CsMerchantService;
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

class OrderPaidJob implements ShouldQueue
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
        // 更新用户的下单数
        $this->updateUserOrderCount();

        // 如果用户没有被邀请过, 将用户的邀请人设置为当前商户
        $this->handleMerchantInvite();

    }

    /**
     * 更新用户累计订单数量
     */
    public function updateUserOrderCount()
    {
        $order = $this->order;
        $user = UserService::getUserById($order->user_id);
        $user->order_count = OrderService::getOrderCountByUserId($user->id);
        $user->save();
    }

    /**
     * 如果用户没有被邀请过, 将用户的邀请人设置为当前商户
     * @throws \Exception
     */
    private function handleMerchantInvite()
    {
        $order = $this->order;
        // 支付成功, 如果用户没有被邀请过, 将用户的邀请人设置为当前商户
        $userId = $order->user_id;
        if( empty( InviteUserRecord::where('user_id', $userId)->first() ) ){
            $merchantId = $order->merchant_id;
            if ($order->merchant_type == Order::MERCHANT_TYPE_SUPERMARKET && $order->type == Order::TYPE_SUPERMARKET) {
                $merchant = CsMerchantService::getById($merchantId);
                $originType = InviteChannel::ORIGIN_TYPE_CS_MERCHANT;
            } else {
                $merchant = MerchantService::getById($merchantId);
                $originType = InviteChannel::ORIGIN_TYPE_MERCHANT;
            }
            if(empty($merchant)){
                throw new DataNotFoundException('商户信息不存在');
            }
            $inviteChannel = InviteChannelService::getByOriginInfo($merchantId, $originType, $merchant->oper_id);
            InviteUserService::bindInviter($userId, $inviteChannel);
        }
    }

}
