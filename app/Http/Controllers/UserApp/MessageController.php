<?php

namespace App\Http\Controllers\UserApp;

use App\Modules\Message\MessageNoticeService;
use App\Modules\Message\MessageSystemService;
use App\Result;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * App 用户消息控制类
 * Class MessageController
 * @package App\Http\Controllers\UserApp
 */
class MessageController extends Controller
{
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

    public function getNeedViewNum(Request $request)
    {
        $user = $request->get('current_user');
        $count = MessageNoticeService::getNeedViewNumByUserId($user->id);
        return Result::success([
           'total'  =>  $count
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

    public function getSystemDetail( Request $request)
    {
        $this->validate($request,['id'=>'required|numeric'],[
            'id.required'=>'参数不合法',
            'id.numeric'=>'参数不合法'
        ]);
        $system = MessageSystemService::getSystemDetailById($request->get('id'));
        return Result::success([
            'data'   =>  $system
        ]);
    }

}
