<?php

namespace App\Jobs;

use App\Exceptions\DataNotFoundException;
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
        // 处理消费额 消费额逻辑暂时去掉, 需要修改
        // $this->handleUserConsumeQuota($this->order);

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
            $merchant = MerchantService::getById($merchantId);
            if(empty($merchant)){
                throw new DataNotFoundException('商户信息不存在');
            }
            $inviteChannel = InviteChannelService::getByOriginInfo($merchantId, InviteChannel::ORIGIN_TYPE_MERCHANT, $merchant->oper_id);
            InviteUserService::bindInviter($userId, $inviteChannel);
        }
    }


    private function handleUserConsumeQuota($order)
    {
        // 处理消费的用户的消费额
        if(in_array($this->order->status, [1, 2, 3])){
            Log::info('订单号：'.$this->order->order_no. ', 状态：'.$this->order->status. ', 不统计积分！');
            return ;
        }else{
            // 查询该订单是否已经计算过积分返利, 防止重复计算
            if(!empty(
            UserCreditRecord::where('type', UserCreditRecord::TYPE_FROM_SELF)
                ->where('order_no', $this->order->order_no)
                ->first()
            )){
                Log::info('订单号: ' . $this->order->order_no . ' 已计算过积分, 不再重复计算');
                return ;
            }
            try{
                DB::beginTransaction();

                // 处理用户自己的消费额
                $this->handleUserSelfConsumeQuota($order);
                // 获取用户的上级
                $parent = InviteUserService::getParent($order->user_id);

                if (!empty($parent)){
                    $this->handleParentUserConsumeQuota($order, $parent);
                }
                DB::commit();
            }catch (\Exception $e){
                Log::error('订单积分任务执行错误,错误信息:'. $e->getMessage(), [
                    'order' => $this->order->toArray(),
                    'e' => $e
                ]);
                DB::rollBack();
            }
        }

    }

    /**
     * 添加用户自己的消费额记录和总消费额记录
     * @param $order
     */
    private function handleUserSelfConsumeQuota($order)
    {
        // 加消费额记录
        $userConsumeQuotaRecords = new UserConsumeQuotaRecord();
        $userConsumeQuotaRecords->user_id = $order->user_id;
        $userConsumeQuotaRecords->consume_quota = $order->pay_price;
        $userConsumeQuotaRecords->inout_type = 1;
        $userConsumeQuotaRecords->type = UserConsumeQuotaRecord::TYPE_TO_SELF;
        $userConsumeQuotaRecords->order_no = $order->order_no;
        $userConsumeQuotaRecords->consume_user_mobile = $order->notify_mobile;
        $userConsumeQuotaRecords->save();

        // 加总消费额
        $userCredit = UserCredit::where('user_id', $order->user_id)->first();
        if (empty($userCredit)){
            $userCredit = new UserCredit();
            $userCredit->user_id = $order->user_id;
            $userCredit->consume_quota = $order->pay_price;
            $userCredit->save();
        }else {
            $userCredit->consume_quota = DB::raw('consume_quota + ' . $order->pay_price);
            $userCredit->save();
        }
    }

    /**
     * @param $order
     * @param User|Merchant|Oper $parent
     */
    private function handleParentUserConsumeQuota($order, $parent)
    {
        if($parent instanceof User){
            // 如果上级是用户, 按用户逻辑处理

            // 获取消费额换算配置
            $consumeQuotaConvertRatioToParent = SettingService::getValueByKey('consume_quota_convert_ratio_to_parent');
            $consumeQuota = $order->pay_price * $consumeQuotaConvertRatioToParent / 100.0;

            // 加上级用户消费额记录
            $userConsumeQuotaRecords = new UserConsumeQuotaRecord();
            $userConsumeQuotaRecords->user_id = $parent->id;
            $userConsumeQuotaRecords->consume_quota = $consumeQuota;
            $userConsumeQuotaRecords->inout_type = 1;
            $userConsumeQuotaRecords->type = UserConsumeQuotaRecord::TYPE_TO_PARENT;
            $userConsumeQuotaRecords->order_no = $order->order_no;
            $userConsumeQuotaRecords->consume_user_mobile = $order->notify_mobile;
            $userConsumeQuotaRecords->save();

            // 加上级用户总消费额
            $userCredit = UserCredit::where('user_id', $parent->id)->first();
            if (empty($userCredit)){
                $userCredit = new UserCredit();
                $userCredit->user_id = $parent->id;
                $userCredit->consume_quota = $consumeQuota;
                $userCredit->save();
            }else {
                $userCredit->consume_quota = DB::raw('consume_quota + ' . $consumeQuota);
                $userCredit->save();
            }
        }else if($parent instanceof Oper){
            // 如果上级是运营中心, 按运营中心逻辑处理
        }else if($parent instanceof Merchant){
            // 如果上级是商户, 按商户逻辑处理
        }
    }

}
