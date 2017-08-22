<?php
/**
 * User: Evan Lee
 * Date: 2017/6/29
 * Time: 11:29
 */

namespace App\Http\Middleware;

use App\Exceptions\NoPermissionException;
use App\Exceptions\UnloginException;
use App\Modules\BossAuth\BossAuthService;
use Closure;
use Illuminate\Http\Request;

class BossVerifyPermission
{
    /** 不需要登录的url列表 */
    private $needlessLoginUrls = [
        '/api/boss/login',
    ];
    /** 不需要检查权限的地址列表 */
    private $needlessCheckAuthUrls = [];

    public function handle(Request $request, Closure $next){
        // 不需要登录的路由名称列表
        $needlessLoginUrls = $this->needlessLoginUrls;
        // 不需要检查权限的地址列表
        $excludeCheckAuthUrls = $this->needlessCheckAuthUrls;
        $currentPath = '/' . $request->path();

        if(!in_array($currentPath, $needlessLoginUrls)){
            // 验证是否需要登录
            $user = $request->session()->get('boss_user');
            if(empty($user)){
                throw new UnloginException();
            }
            if(!in_array($currentPath, $excludeCheckAuthUrls)){
                $hasPermission = BossAuthService::checkAuthByUrl($user['id'], $currentPath);
                if(!$hasPermission){
                    throw new NoPermissionException('没有权限, 权限地址: ' . $currentPath);
                }

            }
        }

        $response = $next($request);
        return $response;
    }
}