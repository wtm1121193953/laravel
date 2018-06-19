<?php

namespace App\Providers;

use App\Modules\Merchant\Merchant;
use App\Modules\User\UserOpenIdMapping;
use App\Observers\MerchantObserver;
use App\Observers\UserOpenIdMappingObserver;
use Debugbar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 设置默认字符长度, 不然执行 php artisan migrate 时会报错
        Schema::defaultStringLength(191);
        Debugbar::disable();

        // 记录数据库操作日志
        DB::listen(function ($query) {
            Log::debug('sql listen', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ]);
        });

        // 商户模型观察者
        Merchant::observe(MerchantObserver::class);
        // 用户绑定运营中心模型观察者
        UserOpenIdMapping::observe(UserOpenIdMappingObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }
}
