<?php

namespace App\Http\Middleware;

use App\Exceptions\UnloginException;
use Closure;

/**
 * admin端登录过滤器
 * Class AdminLoginFilter
 * @package App\Http\Middleware
 */
class AdminLoginFilter
{
    // 不需要登录的url列表
    protected $publicUrls = [
        'api/admin/login',
        'api/admin/logout',
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!in_array($request->path(), $this->publicUrls)){
            $user = session(config('admin.user_session'));
            if(empty($user)){
                throw new UnloginException();
            }
            $request->attributes->add(['current_user' => $user]);
        }
        return $next($request);
    }
}
