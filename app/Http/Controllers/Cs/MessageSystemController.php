<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/11/25/025
 * Time: 10:27
 */
namespace App\Http\Controllers\Cs;

use App\Modules\Message\MessageSystem;
use App\Modules\Message\MessageSystemService;
use App\Result;
use Illuminate\Http\Request;

class MessageSystemController extends BaseController
{
    public function getSystems(Request $request)
    {
        $allowQueryColumns = ['start_time', 'end_time', 'content', 'object_type'];
        $params = [];                                               // 待查询字段
        foreach ($allowQueryColumns as $k => $v) {
            $params[$v] = $request->get($v, null);
        }
        $params['object_type'] = MessageSystem::OB_TYPE_CS_MERCHANT;

        $data = MessageSystemService::getList($params);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}