<?php
/**
 * 运营中心接口路由
 */

use Illuminate\Support\Facades\Route;

Route::prefix('oper')
    ->namespace('Oper')
    ->middleware('oper')->group(function (){

        Route::post('login', 'LoginController@login');
        Route::post('logout', 'LoginController@logout');

        Route::get('area/tree', 'AreaController@getTree');

        Route::get('merchant/categories/tree', 'MerchantCategoryController@getTree');

        Route::get('inviteChannel/inviteQrcode', 'InviteChannelController@getInviteQrcode');
        Route::get('inviteChannel/downloadInviteQrcode', 'InviteChannelController@downloadInviteQrcode');

        Route::get('invite/statistics/dailyList', 'InviteStatisticsController@dailyList');

        Route::group([], base_path('routes/api/oper/merchant.php'));
        Route::group([], base_path('routes/api/oper/settlements.php'));
        Route::group([], base_path('routes/api/oper/operBizMember.php'));
    });