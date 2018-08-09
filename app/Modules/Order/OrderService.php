<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/25
 * Time: 16:16
 */

namespace App\Modules\Order;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Modules\Dishes\DishesItem;
use App\Modules\User\User;
use App\Modules\UserCredit\UserCreditRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OrderService extends BaseService
{

    /**
     * 查询订单列表
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList(array $params)
    {
        $merchantId = array_get($params, 'merchantId');
        $orderNo = array_get($params, 'orderNo');
        $notifyMobile = array_get($params, 'notifyMobile');
        $keyword = array_get($params, 'keyword');
        $createdAt = array_get($params, 'createdAt');
        $type = array_get($params, 'type');
        $status = array_get($params, 'status');
        $goodsName = array_get($params, 'goodsName');

        $query = Order::where(function(Builder $query){
            $query->where('type', Order::TYPE_GROUP_BUY)
                ->orWhere(function(Builder $query){
                    $query->where('type', Order::TYPE_SCAN_QRCODE_PAY)
                        ->whereIn('status', [4, 6, 7]);
                })->orWhere(function(Builder $query){
                    $query->where('type', Order::TYPE_DISHES);
                });
        });
        if($merchantId > 0){
            $query->where('merchant_id', $merchantId);
        }
        if($orderNo){
            $query->where('order_no','like', "%$orderNo%");
        }
        if($notifyMobile){
            $query->where('notify_mobile', 'like', "%$notifyMobile%");
        }
        if($createdAt){
            $query->whereBetween('created_at', [$createdAt[0], $createdAt[1]]);
        }
        if($type){
            if(is_array($type)){
                $query->whereIn('type',$type);
            }else {
                $query->where('type',$type);
            }
        }
        if($status){
            $query->whereIn('status',$status);
        }
        if($type== 1 && $goodsName){
            $query->where('goods_name', 'like', "%$goodsName%");
        }
        if($keyword){
            $query->where(function (Builder $query) use ($keyword) {
                $query->where('order_no', 'like', "%$keyword%")
                    ->orWhere('notify_mobile', 'like', "%$keyword%");
            });
        }

        $data = $query->orderBy('id', 'desc')->paginate();

        foreach ($data as $key => $item){
            $userCreditRecord = UserCreditRecord::where('user_id', $item->user_id)
                ->where('order_no', $item->order_no)
                ->where('inout_type', UserCreditRecord::INOUT_TYPE_IN)
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

        return $data;
    }

    public static function verifyOrder($merchantId, $verifyCode)
    {

        $order_id = OrderItem::where('verify_code', $verifyCode)
            ->where('merchant_id', $merchantId)
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
            return $order;
        }else{
            throw new BaseResponseException('该订单已退款，不能核销');
        }

    }
}