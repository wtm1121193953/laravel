<?php
/**
 * 业务员端接口路由
 */

use Illuminate\Support\Facades\Route;

Route::prefix('bizer')
    ->namespace('Bizer')
    ->middleware('bizer')->group(function (){

        Route::post('login', 'SelfController@login');
        Route::post('logout', 'SelfController@logout');
        Route::post('register', 'SelfController@register');
//        Route::post('self/modifyPassword', 'SelfController@modifyPassword');
        Route::get('merchants', 'MerchantController@getList');
        Route::get('sms/getVerifyCode', 'SmsController@sendVerifyCode');
        Route::get('merchant/categories/tree', 'MerchantController@getTree');
        Route::get('area/tree', 'AreaController@getTree');
        Route::get('merchant/opers/tree', 'MerchantController@allOperNames');
    });