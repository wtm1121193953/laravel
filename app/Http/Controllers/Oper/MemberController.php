<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/9/19/019
 * Time: 12:24
 */
namespace App\Http\Controllers\Oper;

use App\Exceptions\ParamInvalidException;
use App\Exports\OperInviteExport;
use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteStatisticsService;
use App\Modules\Invite\InviteUserService;
use App\Result;
use Illuminate\Support\Carbon;

class MemberController extends Controller
{

    /**
     * 邀请会员列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList()
    {
        $operId = request()->get('current_user')->oper_id;
        $mobile = request('mobile');
        $invite_channel_id = request('invite_channel_id');
        $orderColumn = request('orderColumn');
        $orderType = request('orderType');

        $data = InviteUserService::operInviteList([
            'origin_id' => $operId,
            'mobile' => $mobile,
            'invite_channel_id' => $invite_channel_id,
            'orderColumn' => $orderColumn,
            'orderType' => $orderType
        ]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取所有渠道
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllChannel()
    {
        $operId = request()->get('current_user')->oper_id;
        $channels = InviteChannelService::allOperInviteChannel($operId,true);

        return Result::success([
            'list' => $channels
        ]);
    }

    /**
     * 导出邀请会员列表
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        $operId = request()->get('current_user')->oper_id;
        $mobile = request('mobile');
        $invite_channel_id = request('invite_channel_id');
        $orderColumn = request('orderColumn');
        $orderType = request('orderType');

        $data = InviteUserService::operInviteList([
            'origin_id' => $operId,
            'mobile' => $mobile,
            'invite_channel_id' => $invite_channel_id,
            'orderColumn' => $orderColumn,
            'orderType' => $orderType
        ],true);

        return (new OperInviteExport($data,$operId))->download('用户列表.xlsx');
    }



    public function statisticsDaily()
    {
        $operId = request()->get('current_user')->oper_id;
        $pageSize = request('pageSize');
        $data = InviteStatisticsService::getDailyStatisticsListByOriginInfo($operId, InviteChannel::ORIGIN_TYPE_OPER, [], $pageSize);
        $total = $data->total();
        // 如果是第一页, 获取当日数据统计并添加到列表中
        if(request('page') <= 1){
            $today = InviteStatisticsService::getTodayStatisticsByOriginInfo($operId, InviteChannel::ORIGIN_TYPE_OPER);
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
        $operId = request()->get('current_user')->oper_id;
        $todayInviteCount = InviteStatisticsService::getTodayInviteCountByOriginInfo($operId, InviteChannel::ORIGIN_TYPE_OPER);
        $totalInviteCount = InviteStatisticsService::getTotalInviteCountByOriginInfo($operId, InviteChannel::ORIGIN_TYPE_OPER);;

        return Result::success([
            'todayInviteCount' => $todayInviteCount,
            'totalInviteCount' => $totalInviteCount,
        ]);
    }

    public function getTotal()
    {
        $timeType = request('timeType');
        $startDate = '';
        $endDate = '';
        switch ($timeType) {
            case 'today':
                $startDate = Carbon::now()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'yesterday':
                $startDate = Carbon::now()->subDay()->startOfDay();
                $endDate = $startDate->copy()->endOfDay();
                break;
            case 'week':
                $startDate = Carbon::now()->subWeek();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'lastMonth':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = $startDate->copy()->endOfMonth();;
                break;
            default:
                throw new ParamInvalidException('参数错误');
                break;
        }

        if($startDate && $startDate instanceof Carbon){
            $startDate = $startDate->format('Y-m-d');
        }
        if($endDate && $endDate instanceof Carbon){
            $endDate = $endDate->format('Y-m-d');
        }
        $operId = request()->get('current_user')->oper_id;
        $total = InviteStatisticsService::getTimeSlotInviteCountByOriginInfo($operId, InviteChannel::ORIGIN_TYPE_OPER, $startDate, $endDate);

        return Result::success([
            'total' => $total,
            'totalInviteCount' => $total,
        ]);
    }
}