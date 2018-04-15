<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 17:11
 */

namespace App\Http\Controllers\User;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Goods\Goods;
use App\Modules\Merchant\Merchant;
use App\Modules\Order\Order;
use App\Modules\Order\OrderItem;
use App\Result;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

class OrderController extends Controller
{

    public function getList()
    {
        $status = request('status');
        $user = request()->get('current_user');
        $data = Order::where('user_id', $user->id)
            ->when($status, function (Builder $query) use ($status){
                $query->where('status', $status);
            })
            ->orderByDesc('id')
            ->paginate();
        $data->each(function ($item) {
            $item->items = OrderItem::where('order_id', $item->id)->get();
        });
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function detail(){
        $this->validate(request(), [
            'order_no' => 'required'
        ]);
        $detail = Order::where('order_no', request('order_no'))->firstOrFail();
        $detail->items = OrderItem::where('order_id', $detail->id)->get();
        return Result::success($detail);
    }

    public function add()
    {
        $this->validate(request(), [
            'goods_id' => 'required|integer|min:1',
            'number' => 'integer|min:1',
        ]);
        $goodsId = request('goods_id');
        $number = request('number', 1);
        $goods = Goods::findOrFail($goodsId);

        $user = request()->get('current_user');

        $merchant = Merchant::findOrFail($goods->merchant_id);
        $oper = request()->get('current_oper');

        $order = new Order();
        $order->oper_id = $oper->id;
        $order->order_no = Order::genOrderNo();
        $order->user_id = $user->id;
        $order->user_name = $user->name ?? '';
        $order->merchant_id = $merchant->id;
        $order->merchant_name = $merchant->name ?? '';
        $order->goods_id = $goodsId;
        $order->goods_name = $goods->name;
        $order->goods_pic = $goods->pic;
        $order->price = $goods->price;
        $order->buy_number = $number;
        $order->status = Order::STATUS_UN_PAY;
        $order->pay_price = $goods->price * $number;

        $order->save();


        if(App::environment() === 'local'){
            // 生成核销码, 线上需要放到支付成功通知中
            $items = [];
            for ($i = 0; $i < $number; $i ++){
                $orderItem = new OrderItem();
                $orderItem->oper_id = $oper->id;
                $orderItem->merchant_id = $merchant->id;
                $orderItem->order_id = $order->id;
                $orderItem->verify_code = OrderItem::createVerifyCode($merchant->id);
                $orderItem->status = 1;
                $orderItem->save();
                $items[] = $orderItem;
            }
            $order->status = Order::STATUS_PAID;
            $order->save();
        }

        return Result::success($order);
    }

    /**
     * 订单退款
     */
    public function refund()
    {
        $this->validate(request(), [
            'order_no' => 'required'
        ]);
        $orderNo = request('order_no');
        $order = Order::where('order_no', $orderNo)->firstOrFail();
        if($order->status != Order::STATUS_PAID){
            throw new BaseResponseException('订单状态不允许退款');
        }
        // todo 发起微信支付退款
        if(true){ // 微信退款成功
            $order->status = Order::STATUS_REFUNDED;
            $order->save();
        }
        return Result::success($order);
    }
}