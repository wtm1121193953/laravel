<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/21
 * Time: 10:57 AM
 */

namespace App\Http\Controllers\UserApp;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Cs\CsMerchant;
use App\Result;

class MiniProgramController extends Controller
{
    public function shareMerchant(){

        $data = [];
        $merchantId  = request('merchant_id',0);
        $merchantType = request('merchant_type',1);//商户类型：1为普通商户，2为超市商户
        if($merchantType == 2){
            $merchantInfo = CsMerchant::where('id',$merchantId)->first();
        }else{
            $merchantInfo = Merchant::where('id',$merchantId)->first();
        }

        if($merchantInfo){
            $data['id'] = $merchantInfo->id;
            $data['name'] = $merchantInfo->name;
            $data['desc'] = $merchantInfo->desc;

            $data['logo'] = $merchantInfo->logo;
            if(!empty($merchantInfo->desc_pic_list)){
                $desc_pic = explode(",", $merchantInfo->desc_pic_list)[0];
            }else{
                $desc_pic = $merchantInfo->logo;
            }
            $data['desc_pic'] = $desc_pic;

            //小程序配置信息：
            $miniProgram = config('platform.miniprogram');
            $data['web_page_url'] = "http://www.qq.com";//兼容低版本网页地址
            $data['miniprogram_type'] = 0;// 正式版:0，测试版:1，体验版:2
            $data['gh_id'] = $miniProgram['gh_id'];// 小程序原始id
            $data['path'] = '/pages/merchant/index/id/'.$data['id'];//小程序页面路径
        }
        return Result::success($data);

    }

}