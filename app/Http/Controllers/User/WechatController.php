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
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WechatController extends Controller
{

    /**
     * 微信登陆操作, 用于获取用户openId, 并绑定token
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function login()
    {
        if(App::environment() === 'local'){
            return Result::success([
                'token' => str_random(),
                'userInfo' => request('code') == 'user' ? User::find(1) : null,
            ]);
        }
        $this->validate(request(), [
            'code' => 'required'
        ]);
        $code = request('code', '');
        $app = WechatService::getWechatMiniAppForOper(request()->get('current_oper')->id);
        $result = $app->auth->session($code);
        if(is_string($result)) $result = json_decode($result, 1);
        Log::alert('wxLogin 返回', $result);
        // 绑定用户openId到token
        $token = str_random(32);
//        Cache::add('open_id_for_token_' . $token, $result['openid']);

        return Result::success([
            'token' => $token,
            'result' => $result,
        ]);
    }
}