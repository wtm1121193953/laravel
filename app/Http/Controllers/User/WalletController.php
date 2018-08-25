<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/23
 * Time: 18:01
 */

namespace App\Http\Controllers\User;


use App\Exceptions\NoPermissionException;
use App\Http\Controllers\Controller;
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
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $data = ConsumeQuotaService::getConsumeQuotaRecordList([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'originId' => request()->get('current_user')->id,
            'originType' => WalletConsumeQuotaRecord::ORIGIN_TYPE_USER,
        ]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
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
}