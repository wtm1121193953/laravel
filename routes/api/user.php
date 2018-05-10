<?php
/**
 * 运营中心接口路由
 */

use App\Http\Middleware\User\UserLoginFilter;
use Illuminate\Support\Facades\Route;

Route::prefix('user')
    ->namespace('User')
    ->middleware('user')->group(function (){

        Route::any('wxLogin', 'WechatController@login');
        Route::any('sms/verify_code', 'SmsController@sendVerifyCode');

        Route::any('login', 'LoginController@login');
        Route::any('logout', 'LoginController@logout');
        Route::any('loginWithSceneId', 'LoginController@loginWithSceneId');

        Route::get('area/tree', 'AreaController@getTree');
        Route::get('area/cites/groupByFirstLetter', 'AreaController@getCityListGroupByFirstLetter');
        Route::get('area/cites/withHot', 'AreaController@getCitiesWithHot');
        Route::get('area/getByGps', 'AreaController@getAreaByGps');

        Route::get('merchant/categories/tree', 'MerchantCategoryController@getTree');
        Route::get('merchants', 'MerchantController@getList');
        Route::get('merchant/detail', 'MerchantController@detail');

        Route::get('goods', 'GoodsController@getList');
        Route::get('goods/detail', 'GoodsController@detail');

        Route::get('orders', 'OrderController@getList')->middleware(UserLoginFilter::class);
        Route::get('order/detail', 'OrderController@detail')->middleware(UserLoginFilter::class);
        Route::any('order/buy', 'OrderController@buy')->middleware(UserLoginFilter::class);
        Route::any('order/pay', 'OrderController@pay')->middleware(UserLoginFilter::class);
        Route::any('order/refund', 'OrderController@refund')->middleware(UserLoginFilter::class);

        Route::any('invite/qrcode', 'InviteChannel@getInviteQrcode')->middleware(UserLoginFilter::class);

    });