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
        Route::post('forgot_password', 'SelfController@forgotPassword');
        Route::post('changeName', 'SelfController@changeName');
        
        Route::get('merchants', 'MerchantController@getList');
        Route::get('sms/getVerifyCode', 'SmsController@sendVerifyCode');
        Route::post('sms/checkVerifyCode', 'SmsController@checkVerifyCode');
        Route::get('merchant/categories/tree', 'MerchantController@getTree');
        Route::get('area/tree', 'AreaController@getTree');
        Route::get('merchant/opers/tree', 'MerchantController@allOperNames');
        
        Route::get('orders', 'OrderController@getList');
        Route::get('order/export', 'OrderController@export');
        Route::get('merchant/allMerchantNames', 'OrderController@allMerchantNames');
        Route::get('opersRecord', 'OperRecordController@getList');
        
        Route::group([], base_path('routes/api/bizer/oper.php'));
        Route::group([], base_path('routes/api/bizer/wallet.php'));
    });