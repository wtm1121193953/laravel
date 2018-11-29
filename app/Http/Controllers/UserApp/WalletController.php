<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/8/31
 * Time: 下午7:46
 */

namespace App\HTTP\Controllers\UserApp;

use App\Exceptions\NoPermissionException;
use App\Http\Controllers\Controller;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Result;
use App\ResultCode;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletService;
use App\Modules\Wallet\ConsumeQuotaService;
use App\Modules\Wallet\WalletConsumeQuotaRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Support\Utils;
use App\Modules\Invite\InviteUserService;
use App\Modules\Sms\SmsVerifyCodeService;
use App\Exceptions\ParamInvalidException;
//用户账单
use App\Modules\Wallet\WalletBill;
use App\Exceptions\BaseResponseException;

class WalletController extends Controller
{
    /**
     * 获取用户钱包信息
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getWallet()
    {
        $value = $this->getUserId();
        $wallet = WalletService::getWalletInfoByOriginInfo($value, Wallet::ORIGIN_TYPE_USER);
        return Result::success($wallet);
    }

    /**
     * 获取账单信息
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getBills()
    {
        $value = $this->getUserId();
        $startDate = request('startDate');
        $endDate = request('endDate');
        $type = request('type');
        if ($type == '0') {
            $type = '';
        }elseif ($type == 7){
            $type = [7,8];
        }
        $bills = WalletService::getBillList([
            'originId' => $value,
            'originType' => WalletBill::ORIGIN_TYPE_USER,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'type' => $type,
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
        if (empty($billInfo)) {
            throw new NoPermissionException('账单信息不存在');
        }
        if ($billInfo->origin_id != $userId && $billInfo->origin_type != WalletBill::ORIGIN_TYPE_USER) {
            throw new NoPermissionException('账单信息不存在');
        }

        return Result::success($billInfo);
    }

    /**
     * 获取消费额记录
     */
    public function getConsumeQuotas()
    {
        $value = $this->getUserId();
        $month = request('month');
        if (empty($month)) {
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
            'originId' => $value,
            'originType' => WalletConsumeQuotaRecord::ORIGIN_TYPE_USER,
        ], $pageSize, true);
        // 当月总消费额
        $amount = $query->sum('consume_quota');
        $data = $query->paginate($pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'amount' => $amount,
        ]);
    }

    /**
     * 获取消费额详情
     */
    public function getConsumeQuotaDetail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = empty(request('id')) ? '' : request('id');
        if (strlen($id) <= 0) {
            return Result::error(PARAMS_INVALID, '输入参数有误');
        }
        $consumeQuota = ConsumeQuotaService::getDetailById($id);

        return Result::success($consumeQuota);
    }

    /**
     * 校验旧密码
     * Author:  zwg
     * Date:    180903
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function confirmPassword()
    {
        $password = request('password');
        $password = Utils::aesDecrypt($password);
        if (!preg_match('/^\d{6}$/',$password)) {
            throw new ParamInvalidException('密码必须是数字');
        }
        $user = request()->get('current_user');
        WalletService::checkPayPassword($password, $user->id);
        // 记录确认密码时间
        $token = str_random();
        Cache::put('user_pay_password_modify_temp_token_' . $user->id, $token, 3);
        return Result::success([
            'temp_token' => $token
        ]);
    }

    /**
     * 重置密码
     * Author:  zwg
     * Date:    180903
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function changePassword()
    {
        $password = request('password');
        $password = Utils::aesDecrypt($password);
        if (!preg_match('/^\d{6}$/',$password)) {
            throw new ParamInvalidException('密码必须是数字');
        }

        $currentUser = request()->get('current_user');

        $wallet = WalletService::getWalletInfoByOriginInfo($currentUser->id, Wallet::ORIGIN_TYPE_USER);

        if (!empty($wallet['withdraw_password'])) {
            // 如果已设置过密码，则走以下逻辑
            $this->validate(request(), [
                'temp_token' => 'required'
            ]);
            $inputToken = request()->get('temp_token');
            $user = request()->get('current_user');
            $tempToken = Cache::get('user_pay_password_modify_temp_token_' . $user->id);
            if (empty($tempToken)) {
                throw new NoPermissionException('您的验证信息已超时, 请返回重新验证');
            }
            if ($tempToken != $inputToken) {
                throw new NoPermissionException('验证信息无效');
            }
            // 删除有效时间，避免重复提交
            Cache::forget('user_pay_password_modify_temp_token_' . $user->id);
        }

        // 重置密码入库
        $res = WalletService::updateWalletWithdrawPassword($wallet, $password);
        if ($res) {
            return Result::success('重置密码成功');
        }
        throw new BaseResponseException('重置密码失败', ResultCode::DB_UPDATE_FAIL);
    }

    /**
     * 校验短信验证码
     * Author:  zwg
     * Date:    180903
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function checkVerifyCode()
    {
        $this->validate(request(), [
            'verify_code' => 'required|size:4'
        ]);
        $verifyCode = request()->get('verify_code');
        $user = request()->get('current_user');
        $result = SmsVerifyCodeService::checkVerifyCode($user->mobile, $verifyCode);
        if ($result) {
            $token = str_random();
            Cache::put('user_pay_password_modify_temp_token_' . $user->id, $token, 3);
            return Result::success([
                'temp_token' => $token
            ]);
        }
        throw new ParamInvalidException('验证码错误');
    }

    /**
     * 通过消费额 获取 tps消费额
     * @param $consume
     * @return float|int
     */
    /*private static function getTpsConsumeByConsume($consume)
    {
        $tpsConsume = Utils::getDecimalByNotRounding($consume / 6 / 6.5 / 4, 2);
        return $tpsConsume;
    }*/

    /**
     * 我的贡献值统计
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getTpsConsume()
    {
        $user = request()->get('current_user');

        $wallet = WalletService::getWalletInfoByOriginInfo($user->id, Wallet::ORIGIN_TYPE_USER);
        $totalTpsConsume = !empty($wallet)?($wallet->consume_quota + $wallet->freeze_consume_quota + $wallet->share_consume_quota + $wallet->share_freeze_consume_quota):0;

        $theMonthTpsConsume = ConsumeQuotaService::getConsumeQuotaRecordList([
            'status' => WalletConsumeQuotaRecord::STATUS_REPLACEMENT,
            'originId' => $user->id,
            'originType' => WalletConsumeQuotaRecord::ORIGIN_TYPE_USER,
            'startDate' => Carbon::now()->startOfMonth(),
            'endDate' => Carbon::now()->endOfMonth(),
        ], 15, true)->sum('consume_quota');

        return Result::success([
            'totalTpsConsume' => Utils::floorDecimal($totalTpsConsume, 2),
            'theMonthTpsConsume' => Utils::floorDecimal($theMonthTpsConsume, 2),
            'showReminder' => 1, // 是否显示提示语 0-不显示 1-显示
        ]);
    }

    /**
     * 获取tps消费额记录
     * @deprecated
     */
    public function getTpsConsumeQuotasList()
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
        // 当月总tps消费额
        $amount = $query->sum('consume_quota');

        $data = $query->paginate($pageSize);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'amount' => $amount,
        ]);
    }

    /**
     * 获取tps消费额详情
     * @deprecated
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
        $ratio = FeeSplittingService::getUserFeeSplittingRatioToSelfByMerchantId($merchantId);
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
            'showReminder' => 1, // 是否显示提示语 0-不显示 1-显示
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
        // 先计算总数在分页
        $totalTpsCredit = $query->sum('sync_tps_credit');
        $data = $query->paginate($pageSize);

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
     * 获取用户origin_id
     */
    private function getUserId()
    {
        $user = request()->get('current_user');
        $value = empty($user->id) ? '' : $user->id;
        return $value;
    }

}