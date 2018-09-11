<?php

namespace App\Jobs;

use App\Exceptions\BaseResponseException;
use App\Modules\Invite\InviteUserService;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
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
        //处理消费额 消费额逻辑需要修改
        $this->handleConsumeQuota($this->order);
        // 处理分润
        $this->handleFeeSplitting();
    }

    /**
     * 处理消费额
     * @param $order
     */
    private function handleConsumeQuota($order)
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
                // 处理消费的用户的消费额
                $this->handleUserSelfConsumeQuota($order);
                // 获取用户的上级
                $parent = InviteUserService::getParent($order->user_id);

                if (!empty($parent)){
                    $this->handleParentUserConsumeQuota($order, $parent);
                }

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
     * @param Order $order
     * @param User|Merchant|Oper $parent
     */
    private function handleParentUserConsumeQuota($order, $parent)
    {
        if($parent instanceof User){
            // 如果上级是用户, 处理用户的消费额

            //查找上级的消费额记录
            $userConsumeQuotaRecord = UserConsumeQuotaRecord::where('user_id', $parent->id)
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
            $userConsumeQuotaRecords->user_id = $parent->id;
            $userConsumeQuotaRecords->consume_quota = $userConsumeQuotaRecord->consume_quota;
            $userConsumeQuotaRecords->inout_type = UserConsumeQuotaRecord::OUT_TYPE;
            $userConsumeQuotaRecords->type = UserConsumeQuotaRecord::TYPE_TO_REFUND;
            $userConsumeQuotaRecords->order_no = $order->order_no;
            $userConsumeQuotaRecords->consume_user_mobile = $order->notify_mobile;
            $userConsumeQuotaRecords->save();

            // 加退款的总消费额
            $userCredit = UserCredit::where('user_id', $parent->id)->first();
            if (empty($userCredit)){
                Log::error('用户积分表中没有用户id为'.$parent->id.'的积分消费额的记录，用户上级退款减消费额失败',[
                    'order' => $order,
                    'parentUser' => $parent
                ]);
                throw new BaseResponseException('用户上级退款减消费额失败');
            }else {
                $userCredit->consume_quota = DB::raw('consume_quota - ' . $userConsumeQuotaRecord->consume_quota);
                $userCredit->save();
            }
        }else if($parent instanceof Merchant) {
            // 如果上级是商户, 处理商户的消费额
        }else if ($parent instanceof Oper){
            // 如果上级是运营中心, 处理运营中心的消费额
        }
    }

    /**
     * 处理分润
     */
    private function handleFeeSplitting()
    {
        // todo
    }

}
