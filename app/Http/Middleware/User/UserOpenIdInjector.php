<?php

namespace App\Http\Middleware\User;

use App\Exceptions\HasNotOpenIdException;
use App\Exceptions\TokenInvalidException;
use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * 注入微信openId
 * Class UserOpenIdInject
 * @package App\Http\Middleware\User
 */
class UserOpenIdInjector
{
    // 不需要注入openId的路径
    protected $publicUrls = [
        'api/user/wxLogin',
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
            $token = $request->get('token');
            if(empty($token)){
                throw new TokenInvalidException();
            }
            $openId = Cache::get('open_id_for_token_' . $token);
            if(empty($openId)){
                throw new HasNotOpenIdException();
            }
            $request->attributes->add(['current_open_id' => $openId]);
        }

        return $next($request);
    }
}
