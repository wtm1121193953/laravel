<?php
/**
 * 运营中心接口路由
 */

use Illuminate\Support\Facades\Route;

Route::prefix('merchant')
    ->namespace('Merchant')
    ->middleware('merchant')->group(function (){

        Route::post('login', 'SelfController@login');
        Route::post('logout', 'SelfController@logout');
        Route::post('self/modifyPassword', 'SelfController@modifyPassword');
        Route::get('self/getMerchantInfo', 'SelfController@getMerchantInfo');

        Route::get('inviteChannel/inviteQrcode', 'InviteChannelController@getInviteQrcode');
        Route::get('inviteChannel/downloadInviteQrcode', 'InviteChannelController@downloadInviteQrcode');

        Route::get('pay/qrcode/miniprogramQrcode', 'PayQrcodeController@getMiniprogramAppCode');
        Route::get('pay/qrcode/downloadMiniprogramQrcode', 'PayQrcodeController@downloadMiniprogramAppCode');

        Route::get('invite/statistics/list', 'InviteStatisticsController@getList');
        Route::get('invite/statistics/dailyList', 'InviteStatisticsController@dailyList');
        Route::get('invite/statistics/downloadInviteRecordList', 'InviteStatisticsController@downloadInviteRecordList');
        Route::get('invite/statistics/getTodayAndTotalInviteNumber', 'InviteStatisticsController@getTodayAndTotalInviteNumber');

        Route::get('sms/getVerifyCode', 'SmsController@sendVerifyCode');

        Route::post('setting/edit', 'MerchantSettingController@edit');
        Route::get('setting/getSetting', 'MerchantSettingController@getSetting');

        Route::get('tps/getBindInfo', 'TpsBindController@getBindInfo');
        Route::post('tps/sendVerifyCode', 'TpsBindController@sendVerifyCode');
        Route::post('tps/bindAccount', 'TpsBindController@bindAccount');

        Route::group([], base_path('routes/api/merchant/goods.php'));
        Route::group([], base_path('routes/api/merchant/orders.php'));
        Route::group([], base_path('routes/api/merchant/settlements.php'));
        Route::group([], base_path('routes/api/merchant/mapping_user.php'));

        Route::group([], base_path('routes/api/merchant/dishes.php'));


    });