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

Route::get('/test/h5', function(){
    $app = \App\Modules\Wechat\WechatService::getWechatMiniAppForOper(3);
    $response = $app->app_code->getUnlimit('id=52', [
        'page' => 'pages/severs/index/index',
        'width' => 300,
    ]);
    $filename = $response->saveAs(storage_path('app/public/miniprogram/app_code'), "3_52.png");
    dump(asset('storage/miniprogram/app_code/' . $filename));
    return view('test_h5', [
        'app_code_url' => asset('storage/miniprogram/app_code/' . $filename)
    ]);
});


Route::post('/upload/image', 'UploadController@image');
