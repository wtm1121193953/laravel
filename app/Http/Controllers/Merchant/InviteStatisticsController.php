<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/10
 * Time: 15:00
 */

namespace App\Http\Controllers\Merchant;


use App\Exports\InviteUserRecordExport;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteStatisticsService;
use App\Modules\Invite\InviteUserStatisticsDaily;
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
        $data = InviteUserStatisticsDaily::where('origin_id', $merchantId)
            ->where('origin_type', InviteChannel::ORIGIN_TYPE_MERCHANT)
            ->orderByDesc('date')
            ->paginate($pageSize);
        // 如果是第一页, 获取当日数据统计并添加到列表中
        if(request('page') <= 1){
            $today = new InviteUserStatisticsDaily();
            $date = date('Y-m-d');
            $today->date = $date;
            $today->invite_count = InviteStatisticsService::getInviteCountByDate(
                $date, $merchantId, InviteChannel::ORIGIN_TYPE_MERCHANT
            );
            if($today->invite_count > 0){
                $data->prepend($today);
            }
        }
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取商户的当日邀请数量和邀请总数量
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getTodayAndTotalInviteNumber()
    {
        $merchantId = request()->get('current_user')->merchant_id;
        $date = date('Y-m-d');
        $todayInviteCount = InviteStatisticsService::getInviteCountByDate(
            $date, $merchantId, InviteChannel::ORIGIN_TYPE_MERCHANT
        );
        $totalInviteCount = InviteStatisticsService::getInviteUserCountByMerchantId($merchantId);

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
        $merchantId = request()->get('current_user')->merchant_id;
        $pageSize = request('pageSize', 15);
        $mobile = request('mobile', '');

        $data = InviteStatisticsService::getInviteRecordListByMerchantId($merchantId, $pageSize, $mobile);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 导出我的会员（商户邀请的用户信息）
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadInviteRecordList()
    {
        $merchantId = request()->get('current_user')->merchant_id;
        $mobile = request('mobile', '');

        return (new InviteUserRecordExport($merchantId, $mobile))->download('我的会员.xlsx');
    }
}