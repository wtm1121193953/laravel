<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/11
 * Time: 0:31
 */

namespace App\Http\Middleware\Merchant;

use App\Exceptions\UnloginException;
use Closure;

/**
 * 运营中心登陆过滤器
 * Class OperLoginFilter
 * @package App\Http\Middleware\Merchant
 */
class MerchantLoginFilter
{

    // 不需要登录的url列表
    protected $publicUrls = [
        'api/merchant/login',
        'api/merchant/logout',
        'api/cs/login',
        'api/cs/logout',
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
            $user = session(config('merchant.user_session'));
            if(empty($user)){
                throw new UnloginException();
            }
            $request->attributes->add(['current_user' => $user]);
        }
        return $next($request);
    }

}