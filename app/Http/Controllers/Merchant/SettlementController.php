<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/15
 * Time: 17:43
 */

namespace App\Http\Controllers\Merchant;


use App\Http\Controllers\Controller;
use App\Modules\Order\Order;
use App\Modules\Settlement\Settlement;
use App\Result;
use Illuminate\Support\Facades\Storage;

class SettlementController extends Controller
{
    public function getList()
    {
        $data = Settlement::where('merchant_id', request()->get('current_user')->merchant_id)
            ->where('amount', '>', 0)
            ->orderBy('id', 'desc')
            ->paginate();

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

    public function download()
    {
        $id = request('id');
        $field = request('field');
        $settlement = Settlement::findOrFail($id);
        $arr = explode("/", $settlement[$field]);
        $img = $arr[count($arr) - 1];
        if($field == 'pay_pic_url'){
            return Storage::download('public/image/item/' . $img, 'cash.png');
        }elseif ($field == 'invoice_pic_url'){
            return Storage::download('public/image/item/' . $img, 'invoice.png');
        }
    }

}