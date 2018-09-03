<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/8/31
 * Time: 下午7:46
 */

namespace App\HTTP\Controllers\UserApp;

use App\Http\Controllers\Controller;
use App\Result;
use App\ResultCode;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletService;
use App\Modules\Wallet\ConsumeQuotaService;
use App\Modules\Wallet\WalletConsumeQuotaRecord;
use Carbon\Carbon;
//用户账单
use App\Modules\Wallet\WalletBill;

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
        $user = request()->get('current_user');
        $bills = WalletService::getBillList([
            'originId' => $value,
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
        $data = $query->paginate($pageSize);
        // 当月总消费额
        $amount = $query->sum('consume_quota');

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
        $id = empty(request('id'))?'':request('id');
        if (strlen($id) <= 0){
            return Result::error(PARAMS_INVALID,'输入参数有误');
        }
        $consumeQuota = ConsumeQuotaService::getDetailById($id);

        return Result::success($consumeQuota);
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