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
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Invite\InviteUserStatisticsDaily;
use App\Result;
use Illuminate\Support\Carbon;

class InviteStatisticsController
{

    public function __construct()
    {
        throw new ParamInvalidException('邀请用户功能已关闭');
    }

    public function dailyList()
    {
        $operId = request()->get('current_user')->oper_id;
        $data = InviteUserStatisticsDaily::where('origin_id', $operId)
            ->where('origin_type', InviteChannel::ORIGIN_TYPE_OPER)
            ->orderByDesc('date')
            ->paginate();
        // 如果是第一页, 获取当日数据统计并添加到列表中
        if(request('page') <= 1){
            $today = new InviteUserStatisticsDaily();
            $date = date('Y-m-d');
            $today->date = $date;
            $today->invite_count = InviteStatisticsService::getInviteCountByDate(
                $date, $operId, InviteChannel::ORIGIN_TYPE_OPER
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
}