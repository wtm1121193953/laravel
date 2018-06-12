<?php

namespace App\Jobs;

use App\Exceptions\BaseResponseException;
use App\Modules\Invite\InviteService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Order\Order;
use App\Modules\User\User;
use App\Modules\User\UserMapping;
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

class OrderRefundJob implements ShouldQueue
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
     */
    public function handle()
    {
        if ($this->order->status != Order::STATUS_REFUNDED){
            Log::info('订单号：'. $this->order->order_no. ', 状态：'. $this->order->status . ', 不统计退款积分和消费额！');
            return;
        }else{
            $userCreditRecord = UserCreditRecord::where('type', UserCreditRecord::TYPE_REFUND)
                ->where('order_no', $this->order->order_no)
                ->first();
            if (!empty($userCreditRecord)){
                Log::info('订单号：'. $this->order->order_no . '已计算过退款积分，不再统计');
                return;
            }

            try{
                DB::beginTransaction();
                //处理消费额
                $this->handleUserConsumeQuota($this->order);
                //处理积分
                $this->handleCredit($this->order);
                DB::commit();
            }catch (\Exception $e){
                Log::error('订单退款积分任务执行错误,错误信息:'. $e->getMessage(), [
                    'order' => $this->order->toArray(),
                    'e' => $e
                ]);
                DB::rollBack();
            }
        }
    }

    /**
     * 处理消费额
     * @param $order
     */
    private function handleUserConsumeQuota($order)
    {
        // 处理消费的用户的消费额
        $this->handleUserSelfConsumeQuota($order);
        // 获取用户的上级
        $parentUser = InviteService::getParentUser($order->user_id);

        if (!empty($parentUser)){
            $this->handleParentUserConsumeQuota($order, $parentUser);
        }
    }

    /**
     * 处理用户自己退款的消费额
     * @param $order
     */
    private function handleUserSelfConsumeQuota($order)
    {
        //查找用户自己的消费额记录
        $userConsumeQuotaRecord = UserConsumeQuotaRecord::where('user_id', $order->user_id)
            ->where('order_no', $order->order_no)
            ->where('type', UserConsumeQuotaRecord::TYPE_TO_SELF)
            ->where('inout_type', UserConsumeQuotaRecord::IN_TYPE)
            ->first();
        if (empty($userConsumeQuotaRecord)){
            Log::error('用户自己退款添加消费额记录时，未找到用户自己的消费额记录', ['order' => $order]);
            throw new BaseResponseException('用户自己退款添加消费额记录时，未找到用户自己的消费额记录');
        }

        // 加退款的消费额记录
        $userConsumeQuotaRecords = new UserConsumeQuotaRecord();
        $userConsumeQuotaRecords->user_id = $order->user_id;
        $userConsumeQuotaRecords->consume_quota = $userConsumeQuotaRecord->consume_quota;
        $userConsumeQuotaRecords->inout_type = UserConsumeQuotaRecord::OUT_TYPE;
        $userConsumeQuotaRecords->type = UserConsumeQuotaRecord::TYPE_TO_REFUND;
        $userConsumeQuotaRecords->order_no = $order->order_no;
        $userConsumeQuotaRecords->consume_user_mobile = $order->notify_mobile;
        $userConsumeQuotaRecords->save();

        // 加退款的总消费额
        $userCredit = UserCredit::where('user_id', $order->user_id)->first();
        if (empty($userCredit)){
            Log::error('用户积分表中没有用户id为'.$order->user_id.'的积分消费额的记录，退款减消费额失败',[
                'order' => $order
            ]);
            throw new BaseResponseException('用户退款减消费额失败');
        }else {
            $userCredit->consume_quota = DB::raw('consume_quota - ' . $userConsumeQuotaRecord->consume_quota);
            $userCredit->save();
        }
    }

    /**
     * 处理用户上级的消费额记录
     * @param $order
     * @param $parentUser
     */
    private function handleParentUserConsumeQuota($order, $parentUser)
    {
        //查找上级的消费额记录
        $userConsumeQuotaRecord = UserConsumeQuotaRecord::where('user_id', $parentUser->id)
            ->where('order_no', $order->order_no)
            ->where('type', UserConsumeQuotaRecord::TYPE_TO_PARENT)
            ->where('inout_type', UserConsumeQuotaRecord::IN_TYPE)
            ->first();
        if (empty($userConsumeQuotaRecord)){
            Log::error('用户自己退款添加上级消费额记录时，未找到用户上级的消费额记录', ['order' => $order]);
            throw new BaseResponseException('用户自己退款添加上级消费额记录时，未找到用户上级的消费额记录');
        }

        // 加退款的用户上级消费额记录
        $userConsumeQuotaRecords = new UserConsumeQuotaRecord();
        $userConsumeQuotaRecords->user_id = $parentUser->id;
        $userConsumeQuotaRecords->consume_quota = $userConsumeQuotaRecord->consume_quota;
        $userConsumeQuotaRecords->inout_type = UserConsumeQuotaRecord::OUT_TYPE;
        $userConsumeQuotaRecords->type = UserConsumeQuotaRecord::TYPE_TO_REFUND;
        $userConsumeQuotaRecords->order_no = $order->order_no;
        $userConsumeQuotaRecords->consume_user_mobile = $order->notify_mobile;
        $userConsumeQuotaRecords->save();

        // 加退款的总消费额
        $userCredit = UserCredit::where('user_id', $parentUser->id)->first();
        if (empty($userCredit)){
            Log::error('用户积分表中没有用户id为'.$parentUser->id.'的积分消费额的记录，用户上级退款减消费额失败',[
                'order' => $order,
                'parentUser' => $parentUser
            ]);
            throw new BaseResponseException('用户上级退款减消费额失败');
        }else {
            $userCredit->consume_quota = DB::raw('consume_quota - ' . $userConsumeQuotaRecord->consume_quota);
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
        $parentUser = InviteService::getParentUser($order->user_id);
        if(!empty($parentUser)){
            $this->handleParentUserCredit($order, $parentUser);
        }
    }

    /**
     * 处理用户自己的积分
     * @param $order
     */
    private function handleUserSelfCredit($order)
    {
        //查找该订单用户自己的积分记录
        $userCredit = UserCreditRecord::where('user_id', $order->user_id)
            ->where('inout_type', UserCreditRecord::IN_TYPE)
            ->where('type', UserCreditRecord::TYPE_FROM_SELF)
            ->where('order_no', $order->order_no)
            ->first();
        if (empty($userCredit)){
            Log::error('用户退款退积分时，未找到该订单的积分记录', ['order' => $order]);
            throw new BaseResponseException('用户退款退积分时，未找到该订单的积分记录');
        }

        $userCreditRecord = new UserCreditRecord();
        $userCreditRecord->user_id = $order->user_id;
        $userCreditRecord->credit = $userCredit->credit;
        $userCreditRecord->inout_type = UserCreditRecord::OUT_TYPE;
        $userCreditRecord->type = UserCreditRecord::TYPE_REFUND;
        $userCreditRecord->user_level = $userCredit->user_level;
        $userCreditRecord->order_no = $order->order_no;
        $userCreditRecord->consume_user_mobile = $order->notify_mobile;
        $userCreditRecord->order_profit_amount = $userCredit->order_profit_amount;
        $userCreditRecord->ratio = $userCredit->ratio;
        $userCreditRecord->credit_multiplier_of_amount = $userCredit->credit_multiplier_of_amount;
        $userCreditRecord->save();

        //减去累计积分
        $userCredit = UserCredit::where('user_id', $order->user_id)->first();
        $userCredit->total_credit = $userCredit->total_credit - $userCredit->credit;
        $userCredit->save();

        UserLevelCalculationJob::dispatch($order->user_id);
    }

    /**
     * 处理用户父级的积分
     * @param $order
     * @param $parentUser
     */
    private function handleParentUserCredit($order, $parentUser)
    {
        $merchantId = UserMapping::where('user_id', $parentUser->id)
            ->where('origin_type', 1)
            ->value('origin_id');
        $type = $merchantId ? UserCreditRecord::TYPE_FROM_MERCHANT_SHARE : UserCreditRecord::TYPE_FROM_SHARE_SUB;

        //查找该订单用户自己的积分记录
        $userCredit = UserCreditRecord::where('user_id', $parentUser->id)
            ->where('inout_type', UserCreditRecord::IN_TYPE)
            ->where('type', $type)
            ->where('order_no', $order->order_no)
            ->first();

        if (empty($userCredit)){
            return;
        }

        $userCreditRecord = new UserCreditRecord();
        $userCreditRecord->user_id = $parentUser->id;
        $userCreditRecord->credit = $userCredit->credit;
        $userCreditRecord->inout_type = UserCreditRecord::OUT_TYPE;
        $userCreditRecord->type = UserCreditRecord::TYPE_REFUND;
        $userCreditRecord->user_level = $userCredit->user_level;
        $userCreditRecord->merchant_level = $userCredit->merchant_level;
        $userCreditRecord->order_no = $order->order_no;
        $userCreditRecord->consume_user_mobile = $order->notify_mobile;
        $userCreditRecord->order_profit_amount = $userCredit->order_profit_amount;
        $userCreditRecord->ratio = $userCredit->ratio;
        $userCreditRecord->credit_multiplier_of_amount = $userCredit->credit_multiplier_of_amount;
        $userCreditRecord->save();

        //减去累计积分
        $userCredit = UserCredit::where('user_id', $parentUser->id)->first();
        $userCredit->total_credit = DB::raw('total_credit - ' . $userCredit->credit);
        $userCredit->save();
    }
}
