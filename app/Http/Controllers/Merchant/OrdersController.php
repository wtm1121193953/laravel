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
use App\Modules\Dishes\DishesItem;
use App\Modules\Order\Order;
use App\Modules\Order\OrderItem;
use App\Modules\User\User;
use App\Modules\UserCredit\UserCreditRecord;
use App\Result;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OrdersController extends Controller
{
    public function getList()
    {
        $keyword = request('keyword');
        $orderNo = request('orderNo');
        $notifyMobile = request('notifyMobile');
        $data = Order::where('merchant_id', request()->get('current_user')->merchant_id)
            ->where(function(Builder $query){
                $query->where('type', Order::TYPE_GROUP_BUY)
                    ->orWhere(function(Builder $query){
                        $query->where('type', Order::TYPE_SCAN_QRCODE_PAY)
                            ->whereIn('status', [4, 6, 7]);
                    })->orWhere(function(Builder $query){
                    $query->where('type', Order::TYPE_DISHES);
                });
            })
            ->when($orderNo, function (Builder $query) use ($orderNo){
                $query->where('order_no','like', "%$orderNo%");
            })
            ->when($notifyMobile, function (Builder $query) use ($notifyMobile){
                $query->where('notify_mobile', 'like', "%$notifyMobile%");
            })
            ->when($keyword, function(Builder $query) use ($keyword){
                $query->where(function (Builder $query) use ($keyword) {
                    $query->where('order_no', 'like', "%$keyword%")
                        ->orWhere('notify_mobile', 'like', "%$keyword%");
                });
            })
            ->orderBy('id', 'desc')->paginate();

        foreach ($data as $key => $item){
            $userCreditRecord = UserCreditRecord::where('user_id', $item->user_id)
                ->where('order_no', $item->order_no)
                ->where('inout_type', UserCreditRecord::IN_TYPE)
                ->where('type', UserCreditRecord::TYPE_FROM_SELF)
                ->first();
            if (!empty($userCreditRecord)){
                $data[$key]['credit'] = $userCreditRecord->credit;
                $data[$key]['user_level_text'] = User::getLevelText($userCreditRecord->user_level);
            }else{
                $data[$key]['credit'] = 0;
                $data[$key]['user_level_text'] = '';
            }
            if ($item->type == 3){
                $dishesItems = DishesItem::where('dishes_id', $item->dishes_id)->get();
                $data[$key]['dishes_items'] = $dishesItems;
            }
        }

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
            throw new BaseResponseException('该消费码不存在');
        }

        $order = Order::findOrFail($order_id);
        if($order['status'] == Order::STATUS_FINISHED){
            throw new BaseResponseException('该消费码已核销');
        }

        if($order['status'] == Order::STATUS_PAID){
            OrderItem::where('order_id', $order_id)
                ->where('merchant_id', request()->get('current_user')->merchant_id)
                ->update(['status' => 2]);

            $order->status = Order::STATUS_FINISHED;
            $order->finish_time = Carbon::now();
            $order->save();

            return Result::success($order);
        }else{
            throw new BaseResponseException('该订单已退款，不能核销');
        }

    }

}