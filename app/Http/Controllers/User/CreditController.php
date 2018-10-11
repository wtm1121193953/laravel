<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/6/1
 * Time: 21:01
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Modules\User\UserService;
use App\Modules\UserCredit\UserConsumeQuotaRecord;
use App\Modules\UserCredit\UserCreditRecord;
use App\Result;

class CreditController extends Controller
{
    /**
     * 获取积分转换率 (百分比)
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function payAmountToCreditRatio()
    {
        $this->validate(request(), [
            'merchantId' => 'required'
        ]);

        $merchantId = request('merchantId');
        $user = request()->get('current_user');
        $userId= $user->id;
        $userLevel = $user->level;

        $creditRatio = UserService::getPayAmountToCreditRatio($merchantId,$userId,$userLevel);

        return Result::success([
            'creditRatio' => $creditRatio,
        ]);
    }

    /**
     * 我的积分列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCreditList()
    {
        $date = request('month');
        if (isset($date) && !empty($date)){
            $year = date('Y', strtotime($date));
            $month = date('m', strtotime($date));
        }else{
            $year = date('Y');
            $month = date('m');
        }
        $user = request()->get('current_user');
        $data = UserCreditRecord::where('user_id', $user->id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->paginate();

        // 获取当月总积分以及总消耗积分
        $totalInCredit = UserCreditRecord::where('user_id', $user->id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('inout_type', 1)
            ->sum('credit');
        $totalOutCredit = UserCreditRecord::where('user_id', $user->id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('inout_type', 2)
            ->sum('credit');

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'totalInCredit' => $totalInCredit,
            'totalOutCredit' => $totalOutCredit,
        ]);
    }

    /**
     * 获取我的累计积分和累计消费额
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getUserCredit()
    {
        $userId = request()->get('current_user')->id;
        $userCredit = UserService::getUserIdCredit($userId);

        return Result::success($userCredit);
    }

    /**
     * 我的消费额列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getConsumeQuotaRecordList()
    {
        $date = request('month');
        if (isset($date) && !empty($date)){
            $year = date('Y', strtotime($date));
            $month = date('m', strtotime($date));
        }else{
            $year = date('Y');
            $month = date('m');
        }
        $user = request()->get('current_user');
        $data = UserConsumeQuotaRecord::where('user_id', $user->id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->paginate();

        // 获取当月总消费额
        $totalInConsumeQuota = UserConsumeQuotaRecord::where('user_id', $user->id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('inout_type', 1)
            ->sum('consume_quota');
        $totalOutConsumeQuota = UserConsumeQuotaRecord::where('user_id', $user->id)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('inout_type', 2)
            ->sum('consume_quota');
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'totalInConsumeQuota' => $totalInConsumeQuota,
            'totalOutConsumeQuota' => $totalOutConsumeQuota,
        ]);
    }
}