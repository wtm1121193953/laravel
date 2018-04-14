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

        if(App::environment() === 'local'){
            $operId = 1;
        }else {

            // 从 referer 中获取appid
            if($referer[0] &&
                $referer = $referer[0] &&
                preg_match('servicewechat.com/(wx[\d0-9a-zA-Z]*)/.*', $referer, $matches)
            ){
                $appid = $matches[1];
            }else {
                $appid = 'wx1abb4cf60ffea6c9';
            }

            $operId = OperMiniprogram::where('appid', $appid)->value('oper_id');
            if(empty($operId)){
                throw new BaseResponseException('微信小程序appid错误', ResultCode::WECHAT_APPID_INVALID);
            }
        }
        $oper = Oper::findOrFail($operId);
        $request->attributes->add(['current_oper' => $oper]);
        return $next($request);
    }
}
