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
use App\Modules\Settlement\Settlement;
use App\Result;

class SettlementController extends Controller
{
    public function getList()
    {
        $data = Settlement::where('oper_id', request()->get('current_user')->oper_id)
            ->orderBy('id', 'desc')
            ->paginate();
        $merchant = Merchant::where('oper_id', request()->get('current_user')->oper_id)
            ->get()->keyBy('id');

        foreach ($data as &$item){
            $item['merchant_name'] = $merchant[$item['merchant_id']]['name'];
        }
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}