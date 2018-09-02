<?php

namespace App\Http\Controllers\User;


use App\Exceptions\BaseResponseException;
use App\Exceptions\NoPermissionException;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteUserService;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Oper\Oper;
use App\Modules\User\User;
use App\Modules\UserCredit\UserCreditSettingService;
use App\Modules\Wallet\ConsumeQuotaService;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletConsumeQuotaRecord;
use App\Modules\Wallet\WalletService;
use App\Result;
use App\ResultCode;
use App\Support\Utils;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Modules\Sms\SmsService;

class WalletController extends Controller
{
    use \App\Modules\User\GenPassword;          // Author:Jerry Date:180830

    /**
     * 获取用户钱包信息
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getWallet()
    {
        $user = request()->get('current_user');

        $wallet = WalletService::getWalletInfoByOriginInfo($user->id, Wallet::ORIGIN_TYPE_USER);

        return Result::success($wallet);
    }

    /**
     * 获取账单信息
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getBills()
    {
        $startDate = request('startDate');
        $endDate = request('endDate');
        $type = request('type');
        $user = request()->get('current_user');
        $bills = WalletService::getBillList([
            'originId' => $user->id,
            'originType' => WalletBill::ORIGIN_TYPE_USER,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'typeArr' => [$type],
        ], 20);

        return Result::success([
            'list' => $bills->items(),
            'total' => $bills->total(),
        ]);
    }

    /**
     * 获取账单详情
     * @return WalletBill|null
     */
    public function getBillDetail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $userId = request()->get('current_user')->id;
        $billInfo = WalletService::getBillDetailById($id);
        if(empty($billInfo)){
            throw new NoPermissionException('账单信息不存在');
        }
        if($billInfo->origin_id != $userId && $billInfo->origin_type != WalletBill::ORIGIN_TYPE_USER){
            throw new NoPermissionException('账单信息不存在');
        }

        return Result::success($billInfo);
    }

    /**
     * 通过消费额 获取 tps消费额
     * @param $consume
     * @return float|int
     */
    private static function getTpsConsumeByConsume($consume)
    {
        $tpsConsume = Utils::getDecimalByNotRounding($consume / 6 / 6.5 / 4 , 2);
        return $tpsConsume;
    }

    /**
     * 我的tps 消费额统计
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getTpsConsumeStatistics()
    {
        $user = request()->get('current_user');

        $wallet = WalletService::getWalletInfoByOriginInfo($user->id, Wallet::ORIGIN_TYPE_USER);
        $totalTpsConsume = ConsumeQuotaService::getConsumeQuotaRecordList([
            'status' => WalletConsumeQuotaRecord::STATUS_REPLACEMENT,
            'originId' => $user->id,
            'originType' => WalletConsumeQuotaRecord::ORIGIN_TYPE_USER,
        ], 15, true)->sum('tps_consume_quota');
        $theMonthTpsConsume = self::getTpsConsumeByConsume($wallet->consume_quota);

        return Result::success([
            'totalTpsConsume' => $totalTpsConsume,
            'theMonthTpsConsume' => $theMonthTpsConsume,
        ]);
    }

    /**
     * 获取tps消费额记录
     */
    public function getTpsConsumeQuotas()
    {
        $month = request('month');
        if(empty($month)){
            $month = date('Y-m');
        }
        $status = request('status');
        $pageSize = request('pageSize', 15);
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $query = ConsumeQuotaService::getConsumeQuotaRecordList([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'originId' => request()->get('current_user')->id,
            'originType' => WalletConsumeQuotaRecord::ORIGIN_TYPE_USER,
        ], $pageSize, true);
        $data = $query->paginate($pageSize);
        // 当月总tps消费额
        $amount = $query->sum('tps_consume_quota');

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'amount' => $amount,
        ]);
    }

    /**
     * 获取tps消费额详情
     */
    public function getTpsConsumeQuotaDetail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $consumeQuota = ConsumeQuotaService::getDetailById($id);

        return Result::success($consumeQuota);
    }

    /**
     * 获取商户订单 分润给用户自己 的 分润系数
     * @return float|int
     */
    public function getUserFeeSplittingRatioToSelf()
    {
        $this->validate(request(), [
            'merchantId' => 'required|integer|min:1'
        ]);
        $merchantId = request('merchantId');
        $feeRatio = UserCreditSettingService::getFeeSplittingRatioToSelfSetting(); // 自反的分润比例
        $merchant = MerchantService::getById($merchantId);
        if (empty($merchant)) {
            throw new BaseResponseException('该商户不存在');
        }
        $settlementRate = $merchant->settlement_rate;
        $ratio = $feeRatio / 100 * ($settlementRate / 100 - ($settlementRate / 100 * 0.06 * 1.12 / 1.06 + $settlementRate / 100 * 0.1 * 0.25 + 0.0068));
        // 返回的是系数 直接乘以金额就好了
        return Result::success([
            'ratio' => $ratio,
        ]);
    }

    /**
     * TPS积分 统计
     */
    public function getTpsCreditStatistics()
    {
        $user = request()->get('current_user');

        $wallet = WalletService::getWalletInfoByOriginInfo($user->id, Wallet::ORIGIN_TYPE_USER);
        $totalSyncTpsCredit = ConsumeQuotaService::getConsumeQuotaRecordList([
            'status' => WalletConsumeQuotaRecord::STATUS_REPLACEMENT,
            'originId' => $user->id,
            'originType' => WalletConsumeQuotaRecord::ORIGIN_TYPE_USER,
        ], 15, true)->sum('sync_tps_credit');

        $contributeToParent = 0;
        $parent = InviteUserService::getParent($user->id);
        if ($parent) {
            if ($parent instanceof User) {
                $originType = WalletConsumeQuotaRecord::ORIGIN_TYPE_USER;
            } elseif ($parent instanceof Merchant) {
                $originType = WalletConsumeQuotaRecord::ORIGIN_TYPE_MERCHANT;
            } elseif ($parent instanceof Oper) {
                $originType = WalletConsumeQuotaRecord::ORIGIN_TYPE_OPER;
            } else {
                throw new BaseResponseException('改状态不存在');
            }
            $contributeToParent = ConsumeQuotaService::getConsumeQuotaRecordList([
                'status' => WalletConsumeQuotaRecord::STATUS_REPLACEMENT,
                'originId' => $user->id,
                'originType' => $originType,
            ], 15, true)->sum('sync_tps_credit');
        }

        return Result::success([
            'totalTpsCredit' => $wallet->total_tps_credit, // 个人消费获得TPS积分
            'totalShareTpsCredit' => $wallet->total_share_tps_credit, // 下级累计贡献TPS积分
            'tpsCreditSum' => $wallet->total_share_tps_credit + $wallet->total_tps_credit, // 总累计TPS积分
            'totalSyncTpsCredit' => $totalSyncTpsCredit, // 已置换
            'contributeToParent' => $contributeToParent, // 累计贡献上级TPS积分
        ]);
    }

    /**
     * 获取tps积分列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getTpsCreditList()
    {
        $month = request('month');
        if(empty($month)){
            $month = date('Y-m');
        }
        $type = request('type', '');
        $pageSize = request('pageSize', 15);
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $originId = request()->get('current_user')->id;
        $query = ConsumeQuotaService::getConsumeQuotaRecordList([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'type' => $type,
            'originId' => $originId,
            'originType' => WalletConsumeQuotaRecord::ORIGIN_TYPE_USER,
            'syncTpsCredit' => true,
        ], $pageSize, true);
        $data = $query->paginate($pageSize);
        $totalTpsCredit = $query->sum('sync_tps_credit');
        $hasSyncTpsCredit = ConsumeQuotaService::getConsumeQuotaRecordList([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'type' => $type,
            'originId' => $originId,
            'originType' => WalletConsumeQuotaRecord::ORIGIN_TYPE_USER,
            'syncTpsCredit' => true,
            'status' => WalletConsumeQuotaRecord::STATUS_REPLACEMENT,
        ], $pageSize, true)->sum('sync_tps_credit');

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'totalTpsCredit' => $totalTpsCredit, // 获得TPS积分
            'hasSyncTpsCredit' => $hasSyncTpsCredit // 置换TPS积分
        ]);
    }

    /**
     * 校验旧密码
     * Author:  Jerry
     * Date:    180831
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function confirmPassword( Request $request )
    {
        $this->validate($request, [
            'password'  =>  'required|numeric'
        ]);
        WalletService::confirmPassword( $request->input('password'), $request->get('current_user')->id);
        // 记录确认密码时间
        session(['confirm_password'=>time()]);
        return Result::success('确认成功');
    }

    /**
     * 重置密码
     * Author:  Jerry
     * Date:    180831
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function changePassword( Request $request )
    {
        $wallet = WalletService::getWalletInfoByOriginInfo($request->get('current_user')->id, Wallet::ORIGIN_TYPE_USER);
        if( !empty($wallet['withdraw_password']) )
        {
            // 如果已设置过密码，则走以下逻辑
            $confirmTime = session('confirm_password');
            if( $confirmTime==0 )
            {
                return Result::error(ResultCode::NO_PERMISSION,'无权操作,请先验证旧密码');
            }
            // 3分钟有效期
            $confirmTime+= 3*60;
            if( $confirmTime < time() )
            {
                return Result::error(ResultCode::NO_PERMISSION,'超过有效期请重新验证旧密码' );
            }
        }
        $this->validate($request, [
            'password'  =>  'required|numeric'
        ]);
        // 重置密码入库
        $res = WalletService::updateWalletWithdrawPassword( $wallet, $request->input('password'));
        if( $res )
        {
            // 删除有效时间，避免重复提交
            session(['confirm_password'=>null]);
            return Result::success('重置密码成功');
        }
        return Result::error(ResultCode::DB_UPDATE_FAIL, '重置密码失败');
    }

    /**
     * 发送短信验证码
     * Author:  Jerry
     * Date:    180831
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function sendVerifyCode(Request $request)
    {
        $sendTime  = session('verify_code_time');
        $minute     = 1;
        $addTime   = $sendTime+60*$minute;
        if( $sendTime!=0 && $addTime>time() )
        {
            return Result::error(ResultCode::NO_PERMISSION, $minute.'分钟内不可重复发送');
        }
        $currentUser = $request->get('current_user');
        $code   = rand(100000,999999);
        SmsService::sendVerifyCode( $currentUser->mobile, $code);
        // 记录发送时间
        session(['verify_code'=> $code]);
        session(['verify_code_time'=>time()]);
        return Result::success(/*['verify_code'=>$code]*/);
    }

    /**
     * 校验短信验证码
     * Author:  Jerry
     * Date:    180831
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function checkVerifyCode( Request $request )
    {
        $sendTime  = $request->session()->get('verify_code_time', 0);
        if( $sendTime==0 )
        {
            return Result::error(ResultCode::NO_PERMISSION, '无权操作');
        }
        $minute    = 3;
        $addTime   = $sendTime+60*$minute;
        // 设置有效时间为3分钟
        if( $addTime < time() )
        {
            return Result::error(ResultCode::NO_PERMISSION, '验证码已过期');
        }
        $code = session('verify_code');
        if( $code!= $request->input('verify_code') )
        {
            return Result::error(ResultCode::NO_PERMISSION, '验证码错误');
        }
        // 记录确认密码时间
        session( ['confirm_password'=>time()] );
        return Result::success('确认成功');
    }


}