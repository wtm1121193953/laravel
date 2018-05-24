<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/15
 * Time: 17:43
 */

namespace App\Http\Controllers\Oper;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Order\Order;
use App\Modules\Settlement\Settlement;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class SettlementController extends Controller
{
    public function getList()
    {
        $merchantId = request('merchantId');
        $data = Settlement::where('oper_id', request()->get('current_user')->oper_id)
            ->where('amount', '>', 0)
            ->when($merchantId, function(Builder $query) use ($merchantId){
                $query->where('merchant_id', $merchantId);
            })
            ->orderBy('id', 'desc')
            ->paginate();
        $merchant = Merchant::where('oper_id', request()->get('current_user')->oper_id)
            ->get()->keyBy('id');

        foreach ($data as &$item){
            $item['merchant_name'] = isset($merchant[$item['merchant_id']]) ? $merchant[$item['merchant_id']]['name'] : '';
        }
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function getSettlementOrders()
    {
        $settlement_id = request('settlement_id');
        $merchant_id = request('merchant_id');

        $data = Order::where('oper_id', request()->get('current_user')->oper_id)
            ->where('settlement_id', $settlement_id)
            ->where('merchant_id', $merchant_id)
            ->orderBy('id', 'desc')->paginate();

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function updateInvoice()
    {
        $id = request('id');
        $settlement = Settlement::findOrFail($id);
        $settlement->invoice_type = request('invoice_type', 0);
        $settlement->invoice_pic_url = request('invoice_pic_url', '');
        $settlement->logistics_name = request('logistics_name', '');
        $settlement->logistics_no = request('logistics_no', '');
        $settlement->save();

        return Result::success($settlement);
    }

    public function updatePayPicUrl()
    {
        $id = request('id');
        $settlement = Settlement::findOrFail($id);
        $settlement->pay_pic_url = request('pay_pic_url', '');
        $settlement->status = 2;
        $settlement->save();

        return Result::success($settlement);
    }
}