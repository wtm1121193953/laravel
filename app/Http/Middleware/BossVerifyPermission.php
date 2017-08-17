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
use Illuminate\Support\Facades\Route;

class BossVerifyPermission
{
    public function handle(Request $request, Closure $next){
        // 不需要登录的路由名称列表
        /*$excludeLoginRouteNames = ['login', 'dev'];
        // 不需要检查权限的地址列表
        $excludeCheckAuthUrls = [];
        $currentPath = '/' . $request->path();
        $currentRouteName   = Route::currentRouteName();

        if(!in_array($currentRouteName, $excludeLoginRouteNames)){
            // 验证是否需要登录
            $user = $request->session()->get('boss_user');
            if(empty($user)){
                throw new UnloginException();
            }
            if(!in_array($currentPath, $excludeCheckAuthUrls)){

//                $hasPermission = BossAuthService::checkAuthByUrl($user['id'], $currentPath);
                $hasPermission = true;
                if(!$hasPermission){
                    throw new NoPermissionException('没有权限, 权限地址: ' . $currentPath);
                }

            }
        }*/

        $response = $next($request);
        return $response;
    }
}