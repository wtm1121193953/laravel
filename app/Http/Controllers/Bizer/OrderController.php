<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/23
 * Time: 22:56
 */

namespace App\Http\Controllers\Bizer;


use App\Exports\OperOrderExport;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Dishes\DishesItem;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

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
            //'startPayTime' => $startPayTime ?? null,
            //'endPayTime' => $endPayTime ?? null,
            'startFinishTime' => request('startTime'),
            'endFinishTime' => request('endTime'),
        ];
        if(empty(request('merchantId'))){//当商户ID 不存在的时候，取当前业务员的所有商户
            $where["merchantId"] = Merchant::where('bizer_id', request()->get('current_user')->id)->where('status', 1)->select('id')->get()->pluck('id')->toArray();
        }
        //echo "<pre>";print_r($where);exit;
        $data = OrderService::getList($where);
        $list = empty($where["merchantId"]) ? [] : $data->items();
        $total= empty($where["merchantId"]) ? [] : $data->total();
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