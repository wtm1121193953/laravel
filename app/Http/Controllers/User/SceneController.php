<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/22
 * Time: 14:12
 */

namespace App\Http\Controllers\User;


use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Wechat\MiniprogramSceneService;
use App\Result;

class SceneController extends Controller
{

    public function getSceneInfo()
    {
        $this->validate(request(), [
            'sceneId' => 'required|integer|min:1',
        ]);
        $sceneId = request('sceneId');
        $scene = MiniprogramSceneService::getById($sceneId);
        if(empty($scene)){
            throw new ParamInvalidException('场景信息不存在');
        }
        $scene->payload = json_decode($scene->payload, 1);
        return Result::success($scene);
    }
}