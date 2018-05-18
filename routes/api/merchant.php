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

        Route::get('inviteChannel/inviteQrcode', 'InviteChannelController@getInviteQrcode');
        Route::get('inviteChannel/downloadInviteQrcode', 'InviteChannelController@downloadInviteQrcode');

        Route::get('invite/statistics/dailyList', 'InviteStatisticsController@dailyList');

        Route::group([], base_path('routes/api/merchant/goods.php'));
        Route::group([], base_path('routes/api/merchant/orders.php'));
        Route::group([], base_path('routes/api/merchant/settlements.php'));
    });