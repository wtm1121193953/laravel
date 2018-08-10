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
use App\Modules\Oper\OperBizMember;
use App\Modules\Order\Order;
use App\Modules\Settlement\Settlement;
use App\Modules\Settlement\SettlementService;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class SettlementController extends Controller
{
    public function getList()
    {
        $status = request('status');
        $showAmount = request('showAmount');
        $settlementDate = request('settlement_date');
        $operBizMemberName = request('oper_biz_member_name');
        $operBizMemberMobile = request('oper_biz_member_mobile');
        if($operBizMemberName){
            $code = OperBizMember::where('name',$operBizMemberName)->pluck('code')->first();
            $merchantId = Merchant::where('oper_biz_member_code',$code)->pluck('id')->first();
        }elseif($operBizMemberMobile){
            $code = OperBizMember::where('mobile',$operBizMemberMobile)->pluck('code')->first();
            $merchantId = Merchant::where('oper_biz_member_code',$code)->pluck('id')->first();
        }else{
            $merchantId = request('merchantId');
        }
        $data = Settlement::where('oper_id', request()->get('current_user')->oper_id)
            ->where('amount', '>', 0)
            ->when($merchantId, function(Builder $query) use ($merchantId){
                $query->where('merchant_id', $merchantId);
            })
            ->when($status, function (Builder $query) use ($status){
                $query->where('status', $status);
            })
            ->when($showAmount, function(Builder $query) {
                $query->where('amount', '>', 0);
            })
            ->when($settlementDate, function (Builder $query) use ($settlementDate){
                $query->whereBetween('created_at', [$settlementDate[0] . ' 00:00:00', $settlementDate[1] . ' 23:59:59']);
            })->orderBy('id', 'desc')->paginate();

        $merchant = Merchant::where('oper_id', request()->get('current_user')->oper_id)->get()->keyBy('id');

        $operBizMember = OperBizMember::where('oper_id', request()->get('current_user')->oper_id)->get()->keyBy('id');
            /*->when($operBizMemberName, function(Builder $query) use ($operBizMemberName){
            $query->where('name', $operBizMemberName);
        })
            ->when($operBizMemberMobile, function(Builder $query) use ($operBizMemberMobile){
                $query->where('mobile', $operBizMemberMobile);
            })*/

        foreach ($data as &$item){
            if(isset($merchant[$item['merchant_id']])){
                $item['merchant_name'] = $merchant[$item['merchant_id']]['name'];
                foreach($operBizMember as &$key){
                    if($merchant[$item['merchant_id']]['oper_biz_member_code'] == $key['code']){
                        $item['oper_biz_member_name'] = $key['name'];
                        $item['oper_biz_member_mobile'] = $key['mobile'];
                    }
                }
            }
        }
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function export()
    {
        $keyword = request('keyword', '');
        $operId = request()->get('current_user')->oper_id;
        $query = SettlementService::getOperInviteChannels($operId, $keyword, true);
        return (new OperInviteChannelExport($query))->download('推广渠道列表.xlsx');
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