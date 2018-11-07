<?php

namespace App\Http\Controllers\User;

use App\Modules\Message\MessageNoticeService;
use App\Result;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * 判断是否需要显示小红点
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function isShowReDot(Request $request)
    {
        $user = $request->get('current_user');
        $lastReadTime = Cache::get('message_last_read_time'.$user->id);
        $exists = Db::table('message_system')
            ->when( $lastReadTime, function ($query) use ($lastReadTime) {
                $query->where('id','>', $lastReadTime);
            })
            ->exists();
        if($exists){
            return Result::success(['is_show'=>true]);
        }
        $count = MessageNoticeService::getNeedViewNumByUserId($user->id);
        if($count>0){
            return Result::success(['is_show'=>true]);
        }
        return Result::success(['is_show'=>false]);
    }
}
