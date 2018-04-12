<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/12
 * Time: 17:11
 */

namespace App\Http\Controllers\User;


use App\Modules\Order\Order;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class OrderController
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
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}