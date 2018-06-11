<?php

namespace App\Jobs;

use App\Modules\Invite\InviteUserRecord;
use App\Modules\Merchant\Merchant;
use App\Modules\Order\Order;
use App\Modules\Setting\SettingService;
use App\Modules\User\User;
use App\Modules\User\UserMapping;
use App\Modules\UserCredit\UserConsumeQuotaRecord;
use App\Modules\UserCredit\UserCredit;
use App\Modules\UserCredit\UserCreditRecord;
use App\Modules\UserCredit\UserCreditSettingService;
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
     * @return void
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
        //
        if(in_array($this->order->status, [1, 2, 3])){
            Log::info('订单号：'.$this->order->order_no. ', 状态：'.$this->order->status. ', 不统计积分！');
            return ;
        }else{
            // 查询该订单是否已经计算过积分返利, 防止重复计算
            if(!empty(
                UserCreditRecord::where('type', 1)
                    ->where('order_no', $this->order->order_no)
                    ->first()
            )){
                Log::info('订单号: ' . $this->order->order_no . ' 已计算过积分, 不再重复计算');
                return ;
            }
            try{
                DB::beginTransaction();
                // 处理消费额
                $this->handleUserConsumeQuota($this->order);
                // 处理积分
                $this->handleCredit($this->order);
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

    private function handleUserConsumeQuota($order)
    {
        // 处理消费的用户的消费额
        $this->handleUserSelfConsumeQuota($order);
        // 获取用户的上级
        $parentUser = $this->getParentUser($order->user_id);

        if (!empty($parentUser)){
            $this->handleParentUserConsumeQuota($order, $parentUser);
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
     * @param $parentUser
     */
    private function handleParentUserConsumeQuota($order, $parentUser)
    {
        // 获取消费额换算配置
        $consumeQuotaConvertRatioToParent = SettingService::getValueByKey('consume_quota_convert_ratio_to_parent');
        $consumeQuota = $order->pay_price * $consumeQuotaConvertRatioToParent / 100.0;

        // 加上级用户消费额记录
        $userConsumeQuotaRecords = new UserConsumeQuotaRecord();
        $userConsumeQuotaRecords->user_id = $parentUser->id;
        $userConsumeQuotaRecords->consume_quota = $consumeQuota;
        $userConsumeQuotaRecords->inout_type = 1;
        $userConsumeQuotaRecords->type = UserConsumeQuotaRecord::TYPE_TO_PARENT;
        $userConsumeQuotaRecords->order_no = $order->order_no;
        $userConsumeQuotaRecords->consume_user_mobile = $order->notify_mobile;
        $userConsumeQuotaRecords->save();

        // 加上级用户总消费额
        $userCredit = UserCredit::where('user_id', $parentUser->id)->first();
        if (empty($userCredit)){
            $userCredit = new UserCredit();
            $userCredit->user_id = $parentUser->id;
            $userCredit->consume_quota = $consumeQuota;
            $userCredit->save();
        }else {
            $userCredit->consume_quota = DB::raw('consume_quota + ' . $consumeQuota);
            $userCredit->save();
        }
    }

    /**
     * 处理积分
     * @param $order
     */
    private function handleCredit($order)
    {
        // 处理消费用户自己的积分
        $this->handleUserSelfCredit($order);

        // 处理上级用户的积分
        // 获取上级
        $parentUser = $this->getParentUser($order->user_id);
        if(!empty($parentUser)){
            $this->handleParentUserCredit($order, $parentUser);
        }
    }

    /**
     * 处理消费用户自己的积分
     * @param $order
     */
    private function handleUserSelfCredit($order)
    {
        // 获取商户的返利比例(即盈利比例)
        $settlementRate = Merchant::where('id', $order->merchant_id)->value('settlement_rate'); //分利比例
        // 计算订单盈利金额
        $profitAmount = $order->pay_price * $settlementRate;
        $userLevel = User::where('id', $order->user_id)->value('level'); //用户等级
        $creditRatio = UserCreditSettingService::getCreditToSelfRatioSetting($userLevel); //自反比例
        $creditMultiplierOfAmount = SettingService::getValueByKey('credit_multiplier_of_amount'); //积分系数
        $credit = $profitAmount * $creditRatio * $creditMultiplierOfAmount / 100 ; //产生积分

        $userCreditRecord = new UserCreditRecord();
        $userCreditRecord->user_id = $order->user_id;
        $userCreditRecord->credit = $credit;
        $userCreditRecord->inout_type = 1;
        $userCreditRecord->type = 1;
        $userCreditRecord->user_level = $userLevel;
        $userCreditRecord->order_no = $order->order_no;
        $userCreditRecord->consume_user_mobile = $order->notify_mobile;
        $userCreditRecord->order_profit_amount = $profitAmount;
        $userCreditRecord->ratio = $creditRatio;
        $userCreditRecord->credit_multiplier_of_amount = $creditMultiplierOfAmount;
        $userCreditRecord->save();

        //添加累计积分
        $userCredit = UserCredit::where('user_id', $order->user_id)->first();
        $userCredit->total_credit = $userCredit->total_credit + $credit;
        $userCredit->save();
    }

    /**
     * 处理上级用户的积分
     * @param $order
     * @param $parentUser
     */
    private function handleParentUserCredit($order, $parentUser)
    {
        //添加积分表记录
        $settlementRate = Merchant::where('id', $order->merchant_id)->value('settlement_rate'); //分利比例
        $settlement = $order->pay_price * $settlementRate;

        $parentLevel = User::where('id', $parentUser->id)->value('level'); //父级用户等级
        // 如果父用户等级是1级[萌新], 则不给父用户返利
        if($parentLevel == 1){
            return ;
        }
        $creditRatio = UserCreditSettingService::getCreditToParentRatioSetting($parentLevel); //分享提成比例

        $merchantId = UserMapping::where('user_id', $parentUser->id)
            ->where('origin_type', 1)
            ->value('origin_id');
        $merchantLevel = 0;
        if ($merchantId){
            // 如果用户关联了商户, 则获取商户等级并根据商户等级获取商户等级加成
            $merchantLevel = Merchant::where('id', $merchantId)->value('level');
            $creditMultiplierOfMerchantLevel = UserCreditSettingService::getCreditMultiplierOfMerchantSetting($merchantLevel); //商户等级加成
            $creditRatio = $creditRatio * $creditMultiplierOfMerchantLevel; // 返利比例
        }

        $creditMultiplierOfAmount = SettingService::getValueByKey('credit_multiplier_of_amount'); //积分系数
        $credit = $settlement * $creditRatio * $creditMultiplierOfAmount / 100.0 ; //产生积分

        $userCreditRecord = new UserCreditRecord();
        $userCreditRecord->user_id = $parentUser->id;
        $userCreditRecord->credit = $credit;
        $userCreditRecord->inout_type = 1;
        $userCreditRecord->type = $merchantId ? 3 : 2;
        $userCreditRecord->user_level = $parentLevel;
        $userCreditRecord->merchant_level = $merchantLevel;
        $userCreditRecord->order_no = $order->order_no;
        $userCreditRecord->consume_user_mobile = $order->notify_mobile;
        $userCreditRecord->order_profit_amount = $settlement;
        $userCreditRecord->ratio = $creditRatio;
        $userCreditRecord->credit_multiplier_of_amount = $creditMultiplierOfAmount;
        $userCreditRecord->save();

        //添加累计积分
        $userCredit = UserCredit::where('user_id', $parentUser->id)->first();
        $userCredit->total_credit = DB::raw('total_credit + ' . $credit);
        $userCredit->save();

    }

    /**
     * 获取用户上级
     * @param $userId
     * @return User
     */
    private function getParentUser($userId)
    {
        $inviteRecord = InviteUserRecord::where('user_id', $userId)->first();
        if(empty($inviteRecord)){
            // 如果没有用户没有上级, 不做任何处理
            return null;
        }
        if($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_USER){
            $user = User::find($inviteRecord->origin_id);
        }else if($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_MERCHANT){
            $userMapping = UserMapping::where('origin_id', $inviteRecord->origin_id)
                ->where('origin_type', UserMapping::ORIGIN_TYPE_MERCHANT)
                ->first();
            if(empty($userMapping)){
                // todo 如果商户没有关联用户, 积分需要另做处理
                return null;
            }
            $user = User::find($userMapping->user_id);
        }else if($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_OPER){
            $userMapping = UserMapping::where('origin_id', $inviteRecord->origin_id)
                ->where('origin_type', UserMapping::ORIGIN_TYPE_OPER)
                ->first();
            if(empty($userMapping)){
                // todo 如果运营中心没有关联用户, 积分需要另做处理
                return null;
            }
            $user = User::find($userMapping->user_id);
        }
        return $user;
    }


}
