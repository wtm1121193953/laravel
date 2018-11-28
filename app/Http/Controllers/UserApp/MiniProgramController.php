<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/21
 * Time: 10:57 AM
 */

namespace App\Http\Controllers\UserApp;
use App\Http\Controllers\Controller;
use App\Modules\Goods\Goods;
use App\Modules\Merchant\Merchant;
use App\Result;

class MiniProgramController extends Controller
{
    public function share(){

        $data = [];
        $query_id = request('id',0);
        $type = request('type');//普通商户：merchant 、 团购商品：goods
        if($type == 'merchant'){
            $merchantInfo = Merchant::where('id',$query_id)->first();
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

                $data['path'] = '/pages/merchant/index/id/'.$data['id'];//小程序页面路径
            }


        }else if($type == "goods"){
            $goodsInfo = Goods::findOrFail($query_id);
            if($goodsInfo){
                $merchantInfo = Merchant::where('id',$goodsInfo->merchant_id)->first();

                $data['id'] = $goodsInfo->id;
                $data['name'] = $goodsInfo->name;
                $data['desc'] = $goodsInfo->desc;
                $data['logo'] = $merchantInfo->logo;
                $data['desc_pic'] = $goodsInfo->thumb_url;

                $data['path'] = 'pages/product/info/id/'.$data['id'];//小程序页面路径
            }
        }
        if($data){
            $miniProgram = config('platform.miniprogram');
            $data['web_page_url'] = "http://www.qq.com";//兼容低版本网页地址
            $data['miniprogram_type'] = 0;// 正式版:0，测试版:1，体验版:2
            $data['gh_id'] = $miniProgram['gh_id'];// 小程序原始id
        }

        return Result::success($data);

    }

}