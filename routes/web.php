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
    $orderNo = request('orderNo');
    if(empty($orderNo)) throw new ParamInvalidException('订单号不能为空');
    $userId = request('userId');
    if(empty($userId)) throw new ParamInvalidException('用户ID不能为空');

    $page = request('page', 'pages/severs/index/index');

    $scene = new MiniprogramScene();
    $scene->oper_id = $targetOperId;
    $scene->page = $page;
    $scene->type = 1;
    $scene->payload = json_encode([
        'order_no' => $orderNo,
        'user_id' => $userId
    ]);
    $scene->save();

    try{
        $appCodeUrl = WechatService::genMiniprogramAppCodeUrl($targetOperId, $scene->id, $page);
    }catch (\App\Exceptions\MiniprogramPageNotExistException $e){
        $appCodeUrl = '';
        $errorMsg = '小程序页面不存在或尚未发布';
    }

//    $appCodeUrl = 'https://o2o.niucha.ren/storage/miniprogram/app_code/_3-id=52.jpg';
    return view('miniprogram_bridge.pay', [
        'app_code_url' => $appCodeUrl,
        'errorMsg' => $errorMsg ?? null,
    ]);
});


Route::post('/upload/image', 'UploadController@image');
