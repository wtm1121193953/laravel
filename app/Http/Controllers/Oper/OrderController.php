<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/23
 * Time: 22:56
 */

namespace App\Http\Controllers\Oper;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Order\Order;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class OrderController extends Controller
{

    public function getList()
    {
        $orderNo = request('orderNo');
        $mobile = request('mobile');
        $merchantId = request('merchantId');
        $timeType = request('timeType', 'payTime');
        $startTime = request('startTime');
        $endTime = request('endTime');

        $query = Order::where('oper_id', request()->get('current_user')->oper_id)
            ->when($orderNo, function(Builder $query) use ($orderNo){
                $query->where('order_no', $orderNo);
            })
            ->when($mobile, function (Builder $query) use ($mobile){
                $query->where('notify_mobile', 'like', "%$mobile%");
            })
            ->when($merchantId, function (Builder $query) use ($merchantId){
                $query->where('merchant_id', $merchantId);
            })
            ->where(function(Builder $query){
                $query->where('type', Order::TYPE_GROUP_BUY)
                    ->orWhere(function(Builder $query){
                        $query->where('type', Order::TYPE_SCAN_QRCODE_PAY)
                            ->whereIn('status', [4, 6, 7]);
                    });
            });

        if($timeType == 'payTime'){
            $timeColumn = 'pay_time';
        }else {
            $timeColumn = 'finish_time';
        }
        if($startTime && $endTime){
            $query->whereBetween($timeColumn, [$startTime, $endTime]);
        }else if($startTime){
            $query->where($timeColumn, '>', $startTime);
        }else if($endTime){
            $query->where($timeColumn, '<', $endTime);
        }

        $data = $query->orderByDesc('id')
            ->paginate();
        $merchantIds = $data->pluck('merchant_id');
        $merchants = Merchant::whereIn('id', $merchantIds->all())->get(['id', 'name'])->keyBy('id');
        $data->each(function($item) use ($merchants){
            $item->merchant_name = isset($merchants[$item->merchant_id]) ? $merchants[$item->merchant_id]->name : '';
        });
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}