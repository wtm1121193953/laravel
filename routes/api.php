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

// 微信支付回调
Route::any('/pay/notify', 'PayController@notify');

/**
 * 加载后台接口路由
 */
Route::group([], base_path('routes/api/admin.php'));
Route::group([], base_path('routes/api/oper.php'));
Route::group([], base_path('routes/api/merchant.php'));
Route::group([], base_path('routes/api/user.php'));