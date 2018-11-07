<?php

namespace App\Http\Middleware\User;

use App\Exceptions\BaseResponseException;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperMiniprogram;
use App\ResultCode;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/**
 * 注入当前小程序所属的运营中心
 * Class UserOpenIdInject
 * @package App\Http\Middleware\User
 */
class CurrentOperInjector
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $referer = $request->header('referer');

        // 从 referer 中获取appid
        if($referer[0] &&
            $referer = $referer[0] &&
            preg_match('/servicewechat.com\/(wx[\d0-9a-zA-Z]*)\/.*/', $referer, $matches)
        ){
            $appid = $matches[1];
        }else {
            throw new BaseResponseException('微信小程序appid错误', ResultCode::WECHAT_APPID_INVALID);
            $appid = 'wx8d0f5e945df699c2';
        }

        if($appid == config('platform.miniprogram.app_id')){
            // 官方小程序
            $request->attributes->add(['current_oper_id' => 0]);
        }else if($appid == config('platform.miniprogram.old.app_id') && (!strstr(url()->full(),'https://xiaochengxu.niucha.ren'))) {
            // 如果为测试服，则不跑以下条件，便于调试
            // 旧的官方小程序
            $request->attributes->add(['current_oper_id' => -1]);
        }else {
            $operId = OperMiniprogram::where('appid', $appid)->value('oper_id');
            if(empty($operId)){
                throw new BaseResponseException('微信小程序appid错误', ResultCode::WECHAT_APPID_INVALID);
            }
            $oper = Oper::findOrFail($operId);
            $request->attributes->add(['current_oper' => $oper]);
            $request->attributes->add(['current_oper_id' => $oper->id]);
        }

        return $next($request);
    }
}
