<?php
/**
 * 运营中心接口路由
 */

use App\Http\Middleware\User\UserLoginFilter;
use Illuminate\Support\Facades\Route;

Route::prefix('app/user')
    ->namespace('UserApp')
    ->middleware('user_app')->group(function (){

        Route::get('version/last', 'VersionController@last');
        Route::get('versions', 'VersionController@getList');

        Route::any('sms/verify_code', 'SmsController@sendVerifyCode');

        Route::any('login', 'LoginController@login');
        Route::any('logout', 'LoginController@logout');
        Route::any('loginWithSceneId', 'LoginController@loginWithSceneId');

        Route::get('area/tree', 'AreaController@getTree');
        Route::get('area/cities/groupByFirstLetter', 'AreaController@getCityListGroupByFirstLetter');
        Route::get('area/cities/withHot', 'AreaController@getCitiesWithHot');
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
        Route::any('order/scanQrcodePay', 'OrderController@scanQrcodePay')->middleware(UserLoginFilter::class);

        Route::get('invite/qrcode', 'InviteChannelController@getInviteQrcode')->middleware(UserLoginFilter::class);
        Route::get('invite/getInviterByInviteChannelId', 'InviteChannelController@getInviterByChannelId');
        Route::post('invite/bindInviter', 'InviteChannelController@bindInviter');

        Route::get('scene/info', 'SceneController@getSceneInfo');

        Route::get('merchant/dishesCategory', 'MerchantDishesController@getDishesCategory');
        Route::get('merchant/dishesGoods', 'MerchantDishesController@getDishesGoods');
    });