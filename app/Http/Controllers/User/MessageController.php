<?php

namespace App\Http\Controllers\User;

use App\Modules\Message\MessageNoticeService;
use App\Modules\Message\MessageSystem;
use App\Modules\Message\MessageSystemService;
use App\Modules\Message\MessageSystemUserBehaviorRecord;
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
        $pollingTime = 60;  // 轮询时间
        $exists = MessageSystemService::isShowRedDot($request->get('current_user')->id);
        return Result::success(['is_show'=>($exists)?true:false,'polling_time'=>$pollingTime]);
    }

    public function getSystems(Request $request)
    {
        $allowQueryColumns = ['start_time', 'end_time', 'content', 'object_type'];
        $params = [];                                               // 待查询字段
        foreach ($allowQueryColumns as $k => $v) {
            $params[$v] = $request->get($v, null);
        }
        $params['object_type'] = MessageSystem::OB_TYPE_USER;
        $data = MessageSystemService::getSystems($params);
        $list = $data->items();
        // 处理是否已读已阅
        $records = MessageSystemUserBehaviorRecordService::getRecordByUserId($request->get('current_user')->id);
        $isViewIds = empty($records->is_view) ? [] : json_decode($records->is_view);
        $isReadIds = empty($records->is_read) ? [] : json_decode($records->is_read);
        foreach ($list as $k => $v){
            $list[$k]['is_view'] = (!empty($isViewIds) && in_array($v->id,$isViewIds)) ? MessageSystemUserBehaviorRecord::IS_VIEW_VIEWED : MessageSystemUserBehaviorRecord::IS_VIEW_NORMAL;
            $list[$k]['is_read'] = (!empty($isReadIds) && in_array($v->id,$isReadIds)) ? MessageSystemUserBehaviorRecord::IS_READ_READED : MessageSystemUserBehaviorRecord::IS_READ_NORMAL;
        }
        return Result::success([
            'list' => $list,
            'total' => $data->total(),
        ]);
    }

    public function getNotices(Request $request)
    {
        $user = $request->get('current_user');
        $data = MessageNoticeService::getNoticesByUserId($user->id);
        MessageNoticeService::cleanNeedViewByUserId($user->id);     // 清除未阅数据
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function getNoticeDetail( Request $request)
    {
        $this->validate($request,['id'=>'required|numeric'],[
            'id.required'=>'参数不合法',
            'id.numeric'=>'参数不合法'
        ]);
        $user = $request->get('current_user');
        $notice = MessageNoticeService::getNoticeDetailByUserIdNId($user->id,$request->get('id'));
        return Result::success($notice);
    }

    public function getNeedViewNum(Request $request)
    {
        $user = $request->get('current_user');
        $count = MessageNoticeService::getNeedViewNumByUserId($user->id);
        return Result::success([
            'total'  =>  $count
        ]);
    }

    public function getSystemDetail( Request $request)
    {
        $this->validate($request,['id'=>'required|numeric'],[
            'id.required'=>'参数不合法',
            'id.numeric'=>'参数不合法'
        ]);
        $system = MessageSystemService::getSystemDetailById($request->get('id'),$request->get('current_user'));
        return Result::success($system);
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
