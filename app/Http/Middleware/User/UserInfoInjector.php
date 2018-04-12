<?php

namespace App\Http\Middleware\User;

use App\Modules\User\User;
use Closure;

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
        $user = User::where('open_id', $openId)->first();
        if($user){
            $request->attributes->add(['current_user' => $user]);
        }
        return $next($request);
    }
}
