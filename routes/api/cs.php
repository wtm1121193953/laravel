<?php
/**
 * 大千超市接口
 */

use Illuminate\Support\Facades\Route;

Route::prefix('cs')
    ->namespace('Merchant')
    ->middleware('merchant')->group(function (){

        Route::post('login', 'SelfController@login');
        Route::post('logout', 'SelfController@logout');
        Route::post('self/modifyPassword', 'SelfController@modifyPassword');
        Route::get('self/getMerchantInfo', 'SelfController@getMerchantInfo');
        Route::get('self/menus', 'SelfController@getMenus');
        Route::get('self/checkElectronicContract', 'SelfController@checkElectronicContract');
        Route::get('self/getMerchantAndElectronicContract', 'SelfController@getMerchantAndElectronicContract');
        Route::post('self/signElectronicContract', 'SelfController@signElectronicContract');
        Route::get('self/showElectronicContract', 'SelfController@showElectronicContract');

    });

Route::prefix('cs')
    ->namespace('Cs')
    ->middleware('merchant')->group(function (){

        Route::get('/categories', 'CategoryController@getList');
        Route::post('/category/changeStatus', 'CategoryController@changeStatus');
        Route::post('/category/changeSort', 'CategoryController@changeSort');

        Route::get('goods', 'GoodsController@getList');
        Route::get('sub_cat', 'GoodsController@getSubCat');
        Route::get('goods/detail', 'GoodsController@detail');
        Route::post('goods/add', 'GoodsController@add');
        Route::post('goods/edit', 'GoodsController@edit');
        Route::post('goods/fastEdit', 'GoodsController@fastEdit');
        Route::post('goods/changeStatus', 'GoodsController@changeStatus');
        Route::post('goods/del', 'GoodsController@del');
        Route::post('goods/modifySort', 'GoodsController@modifySort');

        Route::get('setting/getDeliverySetting', 'SettingController@getDeliverySetting');
        Route::post('setting/saveDeliverySetting', 'SettingController@saveDeliverySetting');


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

        Route::group([], base_path('routes/api/cs/goods.php'));
        Route::group([], base_path('routes/api/cs/orders.php'));
        Route::group([], base_path('routes/api/cs/settlements.php'));
        Route::group([], base_path('routes/api/cs/mapping_user.php'));
        Route::group([], base_path('routes/api/cs/dishes.php'));
        Route::group([], base_path('routes/api/cs/wallet.php'));
    });
Route::get('/merchant/message/systems', 'Admin\MessageSystemController@getSystems')->middleware('merchant');