<?php
/**
 * 运营中心接口路由
 */

use Illuminate\Support\Facades\Route;

Route::prefix('user')
    ->namespace('User')
    ->middleware('user')->group(function (){

        Route::post('sms/verify_code', 'SmsController@sendVerifyCode');
        Route::post('login', 'LoginController@login');

        Route::get('area/tree', 'AreaController@getTree');

        Route::get('merchant/categories/tree', 'MerchantCategoryController@getTree');
        Route::get('merchants', 'MerchantController@getList');
        Route::get('merchant/goods', 'MerchantController@getGoods');

        Route::get('orders', 'OrderController@getList');
        Route::get('order/detail', 'OrderController@detail');
        Route::get('order/add', 'OrderController@add');
//        Route::get('order/pay', 'OrderController@pay');

    });