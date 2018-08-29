<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/23
 * Time: 18:01
 */

namespace App\Http\Controllers\User;


use App\Exceptions\BaseResponseException;
use App\Exceptions\NoPermissionException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantService;
use App\Modules\UserCredit\UserCreditSettingService;
use App\Modules\Wallet\ConsumeQuotaService;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletConsumeQuotaRecord;
use App\Modules\Wallet\WalletService;
use App\Result;
use Illuminate\Support\Carbon;

class WalletController extends Controller
{

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
     * 获取消费额记录
     */
    public function getConsumeQuotas()
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
        // 当月总消费额
        $amount = 0;
        $query->chunk(100, function ($items) use (&$amount) {
            foreach ($items as $item) {
                $amount += $item->consume_quota;
            }
        });

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
}