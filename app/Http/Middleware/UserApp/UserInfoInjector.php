<?php

namespace App\Http\Middleware\UserApp;

use App\Modules\User\User;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

/**
 * 用户端APP注入用户信息, 可能不存在
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
        $token = $request->headers->get('token');
        if($token){
            $user = Cache::get('token_to_user_' . $token);
            if(!empty($user)){
                $request->attributes->add(['current_user' => $user]);
            }
        }
        return $next($request);
    }
}
