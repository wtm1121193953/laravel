<?php

namespace App\Http\Controllers\Bizer;

use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Order\OrderService;
use App\Result;

class OrderController extends Controller
{
     function index(){
         
     }
    public function getList()
    {
        $where = [
            "orderNo"=>request('orderId'),
            "type"=>request('order_type'),
            "goodsName"=>request('goodsName'),
            "merchantId"=>request('merchantId'),
            "operId"=>request('operId'),
            'startCreatedAt' => request('startTime'),
            'endCreatedAt' => request('endTime'),
        ];
        //当商户ID 不存在的时候，取当前业务员的所有商户
        if(empty(request('merchantId'))){
            $where["merchantId"] = Merchant::where('bizer_id', request()->get('current_user')->id)->where('status', 1)->select('id')->get()->pluck('id')->toArray();
        }

        $data = OrderService::getList($where);
        $list = empty($where["merchantId"]) ? [] : $data->items();
        $total= empty($where["merchantId"]) ? 0 : $data->total();

        return Result::success([
            'list' => $list,
            'total' => $total,
        ]);
    }

     /**
     * 获取业务员全部的商户名称
     */
    public function allMerchantNames()
    {
        $data = [
            'bizer_id'=>request()->get('current_user')->id,//登录所属业务员ID
            'audit_status' => request('audit_status'),
            'status' => request('status'),
        ];
        $list = MerchantService::getAllNames($data);
        return Result::success([
            'list' => $list
        ]);
    }
}