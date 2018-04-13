<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/13
 * Time: 12:34
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Modules\User\User;
use App\Modules\Wechat\WechatService;
use App\Result;

class WechatController extends Controller
{

    /**
     * 微信登陆操作, 用于获取用户openId, 并绑定token
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function login()
    {
        return Result::success([
            'token' => str_random(),
            'userInfo' => request('code') == 'user' ? User::find(1) : null,
        ]);
        $this->validate(request(), [
            'code' => 'required'
        ]);
        $code = request('code', '');
        $app = WechatService::getWechatMiniAppForOper(request()->get('current_oper')->id);
        $reuslt = $app->auth->session($code);
        return Result::success($reuslt);
    }
}