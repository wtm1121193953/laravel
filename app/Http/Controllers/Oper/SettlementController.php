<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/15
 * Time: 17:43
 */

namespace App\Http\Controllers\Oper;


use App\Exceptions\NoPermissionException;
use App\Exports\OperSettlementExport;
use App\Http\Controllers\Controller;
use App\Modules\Order\OrderService;
use App\Modules\Settlement\SettlementService;
use App\Result;

class SettlementController extends Controller
{
    public function getList()
    {
        $status = request('status');
        $showAmount = request('showAmount');
        $settlementDate = request('settlement_date');
        $operBizMemberName = request('oper_biz_member_name');
        $operBizMemberMobile = request('oper_biz_member_mobile');
        $merchantId = request('merchantId');
        $operId = request()->get('current_user')->oper_id;

        $data = SettlementService::getOperSettlements($operId, $merchantId, $status, $showAmount, $settlementDate, $operBizMemberName, $operBizMemberMobile);
        //DB::enableQueryLog();
        /*$data = Settlement::where('oper_id', request()->get('current_user')->oper_id)
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
        //var_dump(DB::getQueryLog());

        $merchant = Merchant::where('oper_id', request()->get('current_user')->oper_id)->get()->keyBy('id');

        $operBizMember = OperBizMember::where('oper_id', request()->get('current_user')->oper_id)->when($operBizMemberName, function(Builder $query) use ($operBizMemberName){
            $query->where('name', $operBizMemberName);
        })
            ->when($operBizMemberMobile, function(Builder $query) use ($operBizMemberMobile){
                $query->where('mobile', $operBizMemberMobile);
            })->get()->keyBy('id');

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
        }*/
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function export()
    {

        $merchantId = request('merchantId', '');
        $status = request('status', '');
        $showAmount = request('showAmount', '');
        $settlementDate = explode(',',request('settlementDate', ''));
        $operBizMemberName = request('operName', '');
        $operBizMemberMobile = request('operMobile', '');
        //var_dump($settlementDate);die();
        $operId = request()->get('current_user')->oper_id;
        $query = SettlementService::getOperSettlements($operId, $merchantId, $status, $showAmount, $settlementDate, $operBizMemberName, $operBizMemberMobile, true);
        return (new OperSettlementExport($query))->download('财务管理列表.xlsx');
    }

    public function getSettlementOrders()
    {
        $settlement_id = request('settlement_id');
        $operId = request()->get('current_user')->oper_id;
        $settlement = SettlementService::getById($settlement_id);
        if($settlement->oper_id != $operId){
            throw new NoPermissionException('数据不存在');
        }

        $data = OrderService::getListByOperSettlementId($settlement_id);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function updateInvoice()
    {
        $id = request('id');
        $invoice_type = request('invoice_type', 0);
        $invoice_pic_url = request('invoice_pic_url', '');
        $logistics_name = request('logistics_name', '');
        $logistics_no = request('logistics_no', '');

        $settlement = SettlementService::updateInvoice($id,$invoice_type,$invoice_pic_url,$logistics_name,$logistics_no);

        return Result::success($settlement);
    }

    public function updatePayPicUrl()
    {
        $id = request('id');
        $pay_pic_url = request('pay_pic_url', '');

        $settlement = SettlementService::updatePayPicUrl($id,$pay_pic_url);

        return Result::success($settlement);
    }
}