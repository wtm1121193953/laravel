<?php

namespace App\Http\Middleware\User;


use App\Modules\Oper\OperMiniprogram;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

/**
 * 模拟微信环境, 在请求中注入小程序appid 以及 token
 * Class MockMiniprogramEnv
 * @package App\Http\Middleware\User
 */
class MockMiniprogramEnv
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(App::environment() === 'local' || $request->get('mock')){

            // 注入 referer
            if(!$request->get('oper_id')
                || !$appid = OperMiniprogram::where('oper_id', $request->get('oper_id'))->value('appid')){
                $appid = 'wx8d0f5e945df699c2';
            }
            $request->headers->add([
                'referer' => "https://servicewechat.com/$appid/xxxxx"
            ]);
            // 注入token
            $token = 'mock_token';
            $request->attributes->add([
                'token' => $token
            ]);
            // 绑定token与openId的关联
            if(! $openid = $request->get('open_id')) $openid = 'mock_open_id';
            Cache::put('open_id_for_token_' . $token, $openid, 60 * 24 * 30);

        }
        return $next($request);
    }
}