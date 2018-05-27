<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/27
 * Time: 14:53
 */

namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantSetting;
use App\Result;

class SettingController extends Controller
{
    /**
     * 商户系统设置 添加和修改
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function edit()
    {
        $data = request('form');
        $info = request('info');
        foreach ($data as $key => $value){
            $setting = MerchantSetting::where('merchant_id', request()->get('current_user')->merchant_id)
                ->where('key', $key)
                ->first();
            if (empty($setting)){
                $setting = new MerchantSetting();
                $setting->oper_id = request()->get('current_user')->oper_id;
                $setting->merchant_id = request()->get('current_user')->merchant_id;
                $setting->key = $key;
                $setting->info = $info[$key];
            }
            $setting->value = $value;
            $setting->save();
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