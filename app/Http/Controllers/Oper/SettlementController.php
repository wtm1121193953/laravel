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
        $bizerId = request('bizerId');
        $memberId = request('memberId');
        $operId = request()->get('current_user')->oper_id;

        $params = compact('merchantId', 'status', 'showAmount', 'settlementDate', 'operBizMemberMobile', 'operBizMemberName', 'bizerId', 'memberId');
        $data = SettlementService::getOperSettlements($operId, $params);

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
        $bizerId = request('bizerId');
        $memberId = request('memberId');
        $operId = request()->get('current_user')->oper_id;

        $params = compact('merchantId', 'status', 'showAmount', 'settlementDate', 'operBizMemberMobile', 'operBizMemberName', 'bizerId', 'memberId');
        $query = SettlementService::getOperSettlements($operId, $params, true);
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