<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/27
 * Time: 14:53
 */

namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantSettingService;
use App\Modules\Merchant\MerchantSetting;
use App\Result;

class MerchantSettingController extends Controller
{
    /**
     * 商户系统设置 添加和修改
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit()
    {
        $data = request()->all([
            'dishes_function'
        ]);
        foreach ($data as $key => $value){
            MerchantSettingService::set(request()->get('current_user')->oper_id, request()->get('current_user')->merchant_id, $key, $value);
        }
        return Result::success();
    }

    /**
     * 获取商户系统设置
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getList()
    {
        $data = MerchantSetting::where('merchant_id', request()->get('current_user')->merchant_id)
            ->get();
        $list = [];
        foreach ($data as $item){
            $list[$item['key']] = $item['value'];
        }
        return Result::success([
            'list' => $list,
        ]);
    }
}