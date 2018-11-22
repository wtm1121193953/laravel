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
use App\Exceptions\ParamInvalidException;
use App\Modules\Wechat\MiniprogramScene;
use App\Modules\Wechat\WechatService;

Route::get('/', function () {
    return redirect('/merchant');
});

Route::view('/developer', 'developer');
// 后端页面
Route::view('/admin', 'admin');

// 运营中心页面
Route::view('/oper', 'oper');

// 商户中心页面
Route::view('/merchant', 'merchant');
// 商户中心h5页面
Route::view('/merchant-h5', 'merchant-h5');
// 超市商户页面
Route::view('/cs', 'cs');
// 超市商户h5页面
Route::view('/cs-h5', 'cs-h5');


// 用户端h5页面
/*Route::get('/user-h5', function () {
    return view('user-h5');
});*/


// 业务员端
Route::view('/bizer', 'bizer');

Route::get('/article/{code}', 'ArticleController@index');

Route::get('/miniprogram_bridge/pay', 'PayController@miniprogramPayBridgeByH5');


Route::post('/upload/image', 'UploadController@image');
Route::get('/download', 'DownloadController@download');
Route::get('/scene', 'SceneController@index')->name('scene');

// app引导下载h5页面
Route::get('/app-download-h5', 'SceneController@index');
