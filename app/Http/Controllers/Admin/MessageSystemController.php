<?php

namespace App\Http\Controllers\Admin;

use App\Modules\Message\MessageSystem;
use App\Modules\Message\MessageSystemService;
use App\Modules\Message\MessageSystemUserBehaviorRecord;
use App\Modules\Message\MessageSystemUserBehaviorRecordService;
use App\Result;
use App\ResultCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

/**
 * Class MessageController
 * 系统消息
 * @package App\Http\Controllers\Admin
 */
class MessageSystemController extends Controller
{
    public function getSystems(Request $request)
    {
        $allowQueryColumns = ['start_time', 'end_time', 'content', 'object_type'];
        $params = [];                                               // 待查询字段
        foreach ($allowQueryColumns as $k => $v) {
            $params[$v] = $request->get($v, null);
        }
        $uri = $request->getRequestUri();
        if (strpos($uri,'bizer')){
            // 如果由业务员请求
            $params['object_type'] = MessageSystem::OB_TYPE_BIZER;
        }else if(strpos($uri,'user')){
            // 用户端
            $params['object_type'] = MessageSystem::OB_TYPE_USER;
            // 添加用户最后查阅时间
            $cacheKey = 'message_last_read_time'.$request->get('current_user')->id;
            if(Cache::has($cacheKey)){
                Cache::forget($cacheKey);
            }
            Cache::put($cacheKey,date('Y-m-d H:i:s'), 60*24*30);

        }else if(strpos($uri, 'merchant')){
            // 商户端
            $params['object_type'] = MessageSystem::OB_TYPE_MERCHANT;
        }else if(strpos($uri, 'oper')){
            // 运营中心
            $params['object_type'] = MessageSystem::OB_TYPE_OPER;
        }

        $data = MessageSystemService::getList($params);
        $list = $data->items();
        if(strpos($uri,'user')){
            // 处理是否已读已阅
            $records = MessageSystemUserBehaviorRecordService::getRecordByUserId($request->get('current_user')->id);
            $isViewIds = empty($records->is_view) ? '' : json_decode($records->is_view);
            $isReadIds = empty($records->is_read) ? '' : json_decode($records->is_read);
            foreach ($list as $k => $v){
                $list[$k]['is_view'] = (!empty($isViewIds) && in_array($v['id'],$isViewIds)) ? MessageSystemUserBehaviorRecord::IS_VIEW_VIEWED : MessageSystemUserBehaviorRecord::IS_VIEW_NORMAL;
                $list[$k]['is_read'] = (!empty($isReadIds) && in_array($v['id'],$isReadIds)) ? MessageSystemUserBehaviorRecord::IS_READ_READED : MessageSystemUserBehaviorRecord::IS_READ_NORMAL;
            }
        }
        return Result::success([
            'list' => $list,
            'total' => $data->total(),
        ]);
    }

    public function addMessage(Request $request)
    {
        $request->validate([
            'title' => 'required|max:20',
            'content' => 'required|max:2000',
            'object_type' => 'required|array'
        ], [
            'title.required' => '标题不可为空',
            'content.required' => '内容不可为空',
            'object_type.required' => '角色不可为空',
            'object_type.array' => '数据不合法',
            'title.max' => '标题不能超过 :max 个字',
            'content.max' => '内容不能超过 :max 个字'
        ]);
        $system = MessageSystemService::createSystem($request->all());
        return Result::success($system);
    }
}
