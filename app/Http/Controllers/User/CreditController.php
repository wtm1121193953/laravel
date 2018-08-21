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
        $userId = request()->get('current_user')->id;

        UserService::getUserCreditList($userId,$year,$month);
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
        $userId = request()->get('current_user')->id;

        UserService::getUserConsumeQuotaRecordList($userId,$year,$month);
    }
}