<?php

namespace App\Http\Controllers\User;

use App\Modules\Message\MessageNoticeService;
use App\Modules\Message\MessageSystemService;
use App\Modules\Message\MessageSystemUserBehaviorRecordService;
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
    public function isShowRedDot(Request $request)
    {
        $user = $request->get('current_user');
        $lastReadTime = Cache::get('message_last_read_time'.$user->id);
        $exists = Db::table('message_system')
            ->when( $lastReadTime, function ($query) use ($lastReadTime) {
                $query->where('created_at','>', $lastReadTime);
            })
            ->exists();
        if($exists){
            return Result::success(['is_show'=>true]);
        }
        $exists = Db::table('message_notice')
            ->when( $lastReadTime, function ($query) use ($lastReadTime) {
                $query->where('created_at','>', $lastReadTime);
            })
            ->exists();
        if($exists){
            return Result::success(['is_show'=>true]);
        }
        return Result::success(['is_show'=>false]);
    }

    public function userBehavior(Request $request)
    {
        $this->validate($request,[
            'type'      =>  'required|in:"is_read","is_view"',
            'ids'       =>  'required'
        ],[
            'type.required'     =>  '类型不能为空',
            'type.in'           =>  '类型必须是is_read或者is_view',
            'ids.required'      =>  '修改ID不可为空',
//            'ids.array'         =>  '修改ID只能为数组'
        ]);
        MessageSystemUserBehaviorRecordService::addRecords($request->get('current_user')->id,$request->get('type'),$request->get('ids'));
        return Result::success();
    }

    public function redDotNumList(Request $request)
    {
        $user = $request->get('current_user');
        return Result::success([
            'system'    =>  MessageSystemService::getRedDotCountsByUserId($user->id),
            'notice'    =>  MessageNoticeService::getRedDotCountsByUserId($user->id)
        ]);
    }
}
