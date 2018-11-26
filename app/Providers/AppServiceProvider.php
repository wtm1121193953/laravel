<?php

namespace App\Providers;

use App\Modules\Cs\CsMerchant;
use App\Modules\Cs\CsMerchantCategory;
use App\Modules\Merchant\Merchant;
use App\Modules\User\UserOpenIdMapping;
use App\Observers\CsMerchantCategoryObserver;
use App\Observers\CsMerchantObserver;
use App\Observers\MerchantObserver;
use App\Observers\UserOpenIdMappingObserver;
use Debugbar;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Validator;

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

        // 开启数据库操作记录
        DB::enableQueryLog();
        // 非生产环境 记录数据库操作日志
        if(!App::environment('production')){
            DB::listen(function ($query) {
                Log::debug('sql listen', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ]);
            });
        }

        // 商户模型观察者
        Merchant::observe(MerchantObserver::class);
        // 超市商户模型观察者
        CsMerchant::observe(CsMerchantObserver::class);
        //
        CsMerchantCategory::observe(CsMerchantCategoryObserver::class);
        // 用户绑定运营中心模型观察者
        UserOpenIdMapping::observe(UserOpenIdMappingObserver::class);

        //扩展身份证验证规则 Author:Jerry Date:180831
        Validator::extend('identitycards', function($attribute, $value, $parameters) {
            return preg_match('/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/', $value);
        });
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
