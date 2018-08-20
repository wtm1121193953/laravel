<?php

namespace App\Http\Middleware\User;

use App\Modules\User\User;
use App\Modules\User\UserService;
use Closure;
use Illuminate\Support\Facades\App;

/**
 * 注入用户信息, 可能不存在
 * Class UserInfoInject
 * @package App\Http\Middleware\User
 */
class UserInfoInjector
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
        $openId = $request->get('current_open_id');
        $user = UserService::getUserByOpenId($openId);
        if($user){
            $request->attributes->add(['current_user' => $user]);
        }else {
            if(App::environment() === 'local'){
                $user = User::firstOrFail();
                $request->attributes->add(['current_user' => $user]);
            }
        }
        return $next($request);
    }
}
