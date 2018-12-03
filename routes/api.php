<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::any('test', function(){
    dump(Session::all());
});

Route::post('/upload/image', 'UploadController@image');
Route::post('/upload/file', 'UploadController@file');
Route::get('/download', 'DownloadController@download');

// 微信支付回调
Route::any('/pay/notify', 'PayController@notify');
// 获取全部的运营中心列表, 用于对外开放运营中心列表, 只提供id与名称
Route::get('/public/opers/all', 'PublicController@allOpers');

//融宝支付
Route::any('/pay/reapalPayNotify', 'PayController@notifyRealpay');
Route::any('/pay/reapalRefundNotify', 'PayController@refundRealpay');

//融宝代付回调
Route::any('/agentPay/reapalNotify', 'AgentPayController@reapalNotify');
Route::any('/agentPay/mockAgentPayNotify', 'AgentPayController@mockAgentPayNotify');

// 模拟支付成功接口, 只有本地才有效
Route::any('/pay/mockPaySuccess', 'PayController@mockPaySuccess');
Route::any('/app/user/pay/mockPaySuccess', 'PayController@mockPaySuccess');
// 支付宝支付回调
Route::any('/pay/alipayNotify', 'PayController@alipayNotify');



/**
 * 加载后台接口路由
 */
Route::group([], base_path('routes/api/admin.php'));
Route::group([], base_path('routes/api/oper.php'));
Route::group([], base_path('routes/api/merchant.php'));
Route::group([], base_path('routes/api/cs.php'));
Route::group([], base_path('routes/api/user.php'));
Route::group([], base_path('routes/api/user_app.php'));

Route::group([], base_path('routes/api/bizer.php'));

Route::group([], base_path('routes/api/developer.php'));