<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/10
 * Time: 15:00
 */

namespace App\Http\Controllers\Merchant;


use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteUserStatisticsDaily;
use App\Result;

class InviteStatisticsController
{

    public function dailyList()
    {
        $data = InviteUserStatisticsDaily::where('origin_id', request()->get('current_user')->oper_id)
            ->where('origin_type', InviteChannel::ORIGIN_TYPE_MERCHANT)
            ->orderByDesc('date')
            ->paginate();
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}