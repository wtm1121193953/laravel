<?php

namespace App\Http\Controllers\Admin;

use App\Modules\Message\MessageSystemService;
use App\Result;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        $data = MessageSystemService::getList($params);
        $list = $data->items();
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
