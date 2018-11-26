<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/10
 * Time: 15:00
 */

namespace App\Http\Controllers\Cs;


use App\Exports\CsInviteUserRecordExport;
use App\Exports\InviteUserRecordExport;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteStatisticsService;
use App\Modules\Invite\InviteUserService;
use App\Result;

class InviteStatisticsController
{
    /**
     * 每日统计
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function dailyList()
    {
        $merchantId = request()->get('current_user')->merchant_id;
        $pageSize = request('pageSize');
        $data = InviteStatisticsService::getDailyStatisticsListByOriginInfo($merchantId, InviteChannel::ORIGIN_TYPE_MERCHANT, [], $pageSize);
        $total = $data->total();
        // 如果是第一页, 获取当日数据统计并添加到列表中
        if(request('page') <= 1){
            $today = InviteStatisticsService::getTodayStatisticsByOriginInfo($merchantId, InviteChannel::ORIGIN_TYPE_MERCHANT);
            if($today->invite_count > 0){
                $data->prepend($today);
                $total = $total + 1;
            }
        }
        return Result::success([
            'list' => $data->items(),
            'total' => $total,
        ]);
    }

    /**
     * 获取商户的当日邀请数量和邀请总数量
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getTodayAndTotalInviteNumber()
    {
        $merchantId = request()->get('current_user')->merchant_id;
        $todayInviteCount = InviteStatisticsService::getTodayInviteCountByMerchantId($merchantId);
        $totalInviteCount = InviteStatisticsService::getTotalInviteCountByMerchantId($merchantId);

        return Result::success([
            'todayInviteCount' => $todayInviteCount,
            'totalInviteCount' => $totalInviteCount,
        ]);
    }

    /**
     * 获取商户邀请记录列表
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getList()
    {
        $cs_merchant_id = request()->get('current_user')->merchant_id;
        $page = request('page', 1);
        $pageSize = request('pageSize', 15);
        $mobile = request('mobile', '');
        $orderColumn = request('orderColumn', null);
        $orderType = request('orderType', null);

        $param = [
            'page' => $page,
            'pageSize' => $pageSize,
            'orderColumn' => $orderColumn,
            'orderType' => $orderType,
            'mobile' => $mobile,
        ];

        $data = InviteUserService::getInviteUsersWithOrderCountByCsMerchantId($cs_merchant_id, $param, false);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 导出我的用户（商户邀请的用户信息）
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadInviteRecordList()
    {
        $cs_merchant_id = request()->get('current_user')->merchant_id;
        $mobile = request('mobile', '');

        return (new CsInviteUserRecordExport($cs_merchant_id, $mobile))->download('我的用户.xlsx');
    }
}