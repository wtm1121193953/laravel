<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/10
 * Time: 15:00
 */

namespace App\Http\Controllers\Merchant;


use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteStatisticsService;
use App\Modules\Invite\InviteUserStatisticsDaily;
use App\Result;

class InviteStatisticsController
{

    public function dailyList()
    {
        $merchantId = request()->get('current_user')->merchant_id;
        $data = InviteUserStatisticsDaily::where('origin_id', $merchantId)
            ->where('origin_type', InviteChannel::ORIGIN_TYPE_MERCHANT)
            ->orderByDesc('date')
            ->paginate();
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
}