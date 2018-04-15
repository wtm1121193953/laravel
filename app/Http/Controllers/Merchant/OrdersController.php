<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/15
 * Time: 13:18
 */

namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;
use App\Modules\Order\Order;
use App\Result;

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
}