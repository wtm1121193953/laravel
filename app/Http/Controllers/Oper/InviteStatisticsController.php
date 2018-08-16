<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/10
 * Time: 15:00
 */

namespace App\Http\Controllers\Oper;


use App\Exceptions\ParamInvalidException;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteStatisticsService;
use App\Result;

class InviteStatisticsController
{

    public function __construct()
    {
        throw new ParamInvalidException('邀请用户功能已关闭');
    }

    public function dailyList()
    {
        $operId = request()->get('current_user')->oper_id;
        $page = request('page');
        $data = InviteStatisticsService::getDailyStaticsByOriginInfo($operId, InviteChannel::ORIGIN_TYPE_OPER);
        $total = $data->total();
        if($page <= 1){
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
}