<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 17:11
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Modules\Order\Order;
use App\Modules\Order\OrderItem;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

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

    public function add()
    {
        return Result::success();
    }

    public function detail(){
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $detail = Order::where('id', request('id'))->first();
        $detail->items = OrderItem::where('order_id', $detail->id)->get();
        return Result::success($detail);
    }
}