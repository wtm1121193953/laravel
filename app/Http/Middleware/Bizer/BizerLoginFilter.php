<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/11
 * Time: 0:31
 */

namespace App\Http\Middleware\Bizer;

use App\Exceptions\UnloginException;
use Closure;

/**
 * 运营中心登陆过滤器
 * Class OperLoginFilter
 * @package App\Http\Middleware\Oper
 */
class BizerLoginFilter
{

    // 不需要登录的url列表
    protected $publicUrls = [
        'api/bizer/login',
        'api/bizer/logout',
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
            $user = session(config('bizer.user_session'));
            if(empty($user)){
                throw new UnloginException();
            }
            $request->attributes->add(['current_user' => $user]);
        }
        return $next($request);
    }

}