<?php

namespace App\Http\Controllers\UserApp;

use App\Modules\User\UserCollectMerchantService;
use App\Result;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * 用户收藏店铺控制器
 * Class UserCollectMerchantController
 * Author:   JerryChan
 * Date:     2018/9/19 12:15
 * @package App\Http\Controllers\UserApp
 */
class UserCollectMerchantController extends Controller
{
    /**
     * 添加商铺收藏
     * Author:   JerryChan
     * Date:     2018/9/19 18:06
     * @param Request $request
     */
    public function add( Request $request )
    {
        $merchantId = $request->get('id');
        // 表单验证
        $request->validate([
            'id' => 'required'
        ], [
            'id.required' => 'ID数据不存在'
        ]);
        UserCollectMerchantService::addCollect($request->get('current_user')->id, $merchantId);
        Result::success('收藏成功');
    }

    public function del( Request $request )
    {
        $merchantId = $request->get('id');
        // 表单验证
        $request->validate([
            'id' => 'required'
        ], [
            'id.required' => 'ID数据不存在'
        ]);
        UserCollectMerchantService::modifyStatus($request->get('current_user')->id, $merchantId);
        Result::success('删除成功');
    }

    public function modifyStatus( Request $request )
    {
//        $mer
    }

    public function getList( Request $request )
    {
        $distance = [
            'lng'               =>  $request->get('lng'),
            'lat'               =>  $request->get('lat'),
            'current_open_id'   =>  $request->get('current_open_id')
        ];
        $list = UserCollectMerchantService::getListByUserId($request->get('current_user')->id, $distance);
        return Result::success('获取成功', ['list' => $list]);
    }
}
