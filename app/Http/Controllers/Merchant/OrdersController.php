<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/15
 * Time: 13:18
 */

namespace App\Http\Controllers\Merchant;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Order\Order;
use App\Modules\Order\OrderItem;
use App\Result;
use Illuminate\Support\Carbon;

class OrdersController extends Controller
{
    public function getList()
    {
        $data = Order::where('merchant_id', request()->get('current_user')->merchant_id)
                ->orderBy('id', 'desc')->paginate();

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function verification()
    {
        $verify_code = request('verify_code');

        $order_id = OrderItem::where('verify_code', $verify_code)
            ->where('merchant_id', request()->get('current_user')->merchant_id)
            ->value('order_id');

        if(!$order_id){
            throw new BaseResponseException('该核销码不存在');
        }

        $order = Order::findOrFail($order_id);
        if($order['status'] == Order::STATUS_FINISHED){
            throw new BaseResponseException('该核销码已核销');
        }

        OrderItem::where('order_id', $order_id)
            ->where('merchant_id', request()->get('current_user')->merchant_id)
            ->update(['status' => 2]);

        $order->status = Order::STATUS_FINISHED;
        $order->finish_time = Carbon::now();
        $order->save();

        return Result::success($order);
    }
}