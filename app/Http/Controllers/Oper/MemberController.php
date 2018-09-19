<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/9/19/019
 * Time: 12:24
 */
namespace App\Http\Controllers\Oper;

use App\Http\Controllers\Controller;
use App\Modules\Invite\InviteUserService;
use App\Result;

class MemberController extends Controller
{

    public function getList()
    {
        $operId = request()->get('current_user')->oper_id;
        $mobile = request('mobile');
        $channel_id = request('channel_id');

        $data = InviteUserService::operInviteList([
            'origin_id' => $operId,
            'mobile' => $mobile,
            'channel_id' => $channel_id
        ]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

}