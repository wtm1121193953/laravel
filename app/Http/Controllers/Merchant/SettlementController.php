<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/15
 * Time: 17:43
 */

namespace App\Http\Controllers\Merchant;


use App\Exceptions\DataNotFoundException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Settlement\SettlementService;
use App\Result;
use Illuminate\Support\Facades\Storage;

class SettlementController extends Controller
{
    public function getList()
    {
        $data = SettlementService::getList([
            'merchantId' => request()->get('current_user')->merchant_id,
        ]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function getSettlementOrders()
    {
        $settlementId = request('settlement_id');
        $merchantId = request()->get('current_user')->merchant_id;
        $settlement = SettlementService::getByIdAndMerchantId($settlementId, $merchantId);
        if(empty($settlement)){
            throw new DataNotFoundException('结算单不存在');
        }

        $data = SettlementService::getSettlementOrders($settlementId);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @deprecated todo 去掉下载二维码操作, 由统一下载控制器下载
     */
    public function download()
    {
        $id = request('id');
        $field = request('field');
        $merchantId = request()->get('current_user')->merchant_id;
        $settlement = SettlementService::getByIdAndMerchantId($id, $merchantId);
        if(empty($settlement)){
            throw new DataNotFoundException('结算单信息不存在');
        }

        $arr = explode("/", $settlement[$field]);
        $img = $arr[count($arr) - 1];
        if($field == 'pay_pic_url'){
            // todo 修改为通过url下载, 去掉path拼装
            return Storage::download('public/image/item/' . $img, 'cash.png');
        }elseif ($field == 'invoice_pic_url'){
            return Storage::download('public/image/item/' . $img, 'invoice.png');
        }
        throw new ParamInvalidException('参数异常');
    }

}