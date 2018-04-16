<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Exceptions\BaseResponseException;
use App\Modules\Wechat\WechatService;

Route::get('/', function () {
    return view('welcome');
});

// 后端页面
Route::get('/admin', function () {
    return view('admin');
});

// 运营中心页面
Route::get('/oper', function () {
    return view('oper');
});

// 商户中心页面
Route::get('/merchant', function () {
    return view('merchant');
});

Route::get('/miniprogram_bridge/pay', function(){
    $targetOperId = request('targetOperId');
    if(empty($targetOperId)) throw new BaseResponseException('targetOperId不能为空');
    $scene = request('scene');
    if(empty($scene)) throw new BaseResponseException('scene不能为空');
    $page = request('page', 'pages/severs/index/index');

    $appCodeUrl = WechatService::genMiniprogramAppCodeUrl($targetOperId, $scene, $page);
    return view('miniprogram_bridge.pay', [
        'app_code_url' => $appCodeUrl
    ]);
});


Route::post('/upload/image', 'UploadController@image');
