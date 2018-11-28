<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/21
 * Time: 10:57 AM
 */

namespace App\Http\Controllers\UserApp;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Goods\Goods;
use App\Modules\Merchant\Merchant;
use App\Result;

class WechatController extends Controller
{
    public function getShareInfo(){

        $this->validate(request(), [
            'type' => 'required|in:merchant,goods'
        ]);
        $type = request('type');//普通商户：merchant 、 团购商品：goods

        $miniprogramShareInfo = [];
        if($type == 'merchant'){
            $merchantId = request('merchantId',0);
            if(empty($merchantId)){
                throw new ParamInvalidException('参数不合法');
            }
            $merchantInfo = Merchant::where('id',$merchantId)->first();
            if($merchantInfo){
                $miniprogramShareInfo['name'] = $merchantInfo->name;
                $miniprogramShareInfo['desc'] = $merchantInfo->desc;
                $miniprogramShareInfo['logo'] = $merchantInfo->logo;
                if(!empty($merchantInfo->desc_pic_list)){
                    $desc_pic = explode(",", $merchantInfo->desc_pic_list)[0];
                }else{
                    $desc_pic = $merchantInfo->logo;
                }
                $miniprogramShareInfo['desc_pic'] = $desc_pic;

                $miniprogramShareInfo['path'] = '/pages/merchant/index?id='.$merchantInfo->id;//小程序页面路径
            }


        }else if($type == "goods"){
            $goodsId = request('goodsId', 0);
            if(empty($goodsId)){
                throw new ParamInvalidException('参数不合法');
            }
            $goodsInfo = Goods::findOrFail($goodsId);
            if($goodsInfo){
                $merchantInfo = Merchant::where('id',$goodsInfo->merchant_id)->first();

                $miniprogramShareInfo['name'] = $goodsInfo->name;
                $miniprogramShareInfo['desc'] = $goodsInfo->desc;
                $miniprogramShareInfo['logo'] = $merchantInfo->logo;
                $miniprogramShareInfo['desc_pic'] = $goodsInfo->thumb_url;

                $miniprogramShareInfo['path'] = 'pages/product/info?id='.$goodsInfo->id;//小程序页面路径
            }
        }
        $miniProgram = config('platform.miniprogram');
        $miniprogramShareInfo['web_page_url'] = "https://o2o.niucha.ren/app-download-h5";//兼容低版本网页地址
        $miniprogramShareInfo['miniprogram_type'] = 0;// 正式版:0，测试版:1，体验版:2
        $miniprogramShareInfo['gh_id'] = $miniProgram['gh_id'];// 小程序原始id

        return Result::success([
            'miniprogram' => $miniprogramShareInfo
        ]);

    }

}