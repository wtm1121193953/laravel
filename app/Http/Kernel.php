<?php

namespace App\Http;

use App\Http\Middleware\AdminLoginFilter;
use App\Http\Middleware\AdminPermissionAuthenticate;
use App\Http\Middleware\Developer\DeveloperLoginFilter;
use App\Http\Middleware\Merchant\MerchantLoginFilter;
use App\Http\Middleware\Oper\OperLoginFilter;
use App\Http\Middleware\User\CurrentOperInjector;
use App\Http\Middleware\User\MockMiniprogramEnv;
use App\Http\Middleware\RequestLog;
use App\Http\Middleware\User\UserInfoInjector;
use App\Http\Middleware\User\UserOpenIdInjector;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            'throttle:600,1',
            'bindings',
            RequestLog::class,
        ],

        // developer 开发总后台接口中间件
        'developer' => [
            DeveloperLoginFilter::class,
        ],

        // admin 后台接口中间件
        'admin' => [
            AdminLoginFilter::class,
            AdminPermissionAuthenticate::class,
        ],

        // 运营中心后台接口中间件
        'oper' => [
            OperLoginFilter::class
        ],

        // merchant 商家后台接口中间件
        'merchant' => [
            MerchantLoginFilter::class
        ],

        // user 用户端(小程序)接口中间件
        'user' => [
            MockMiniprogramEnv::class,
            CurrentOperInjector::class,
            UserOpenIdInjector::class,
            UserInfoInjector::class,
        ],

        // user_app 用户端(App)接口中间件
        'user_app' => [
            \App\Http\Middleware\UserApp\UserInfoInjector::class,
        ],

        // user_app 用户端(App)接口中间件
        'bizer' => [
            \App\Http\Middleware\Bizer\BizerLoginFilter::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];
}
