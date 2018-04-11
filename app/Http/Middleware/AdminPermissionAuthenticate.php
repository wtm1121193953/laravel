<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/6
 * Time: 13:10
 */

namespace App\Http\Middleware;
use App\Exceptions\NoPermissionException;
use App\Modules\Admin\AdminUser;
use Closure;
use Illuminate\Support\Str;

/**
 * Admin端权限认证中间件
 * Class AdminPermissionAuthenticate
 * @package App\Http\Middleware
 */
class AdminPermissionAuthenticate
{
    /** 不需要验证权限的地址列表 */
    private $publicUrls = [
        '/api/admin/login',
        '/api/admin/logout',
        '/api/admin/self/rules',
        '/api/admin/self/modifyPassword',
        '/api/admin/area/tree',
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
        $url = $request->path();
        if(!Str::startsWith($url, '/')){
            $url = '/' . $url;
        }
        if(!in_array($url, $this->publicUrls)){
            /** @var AdminUser $user */
            $user = $request->get('current_user');
            if(!$user->hasPermission($url)){
                throw new NoPermissionException('没有权限, 权限地址: ' . $url);
            }
        }
        return $next($request);
    }
}