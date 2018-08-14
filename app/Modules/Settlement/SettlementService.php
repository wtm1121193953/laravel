<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/25
 * Time: 17:03
 */

namespace App\Modules\Settlement;


use App\BaseService;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\OperBizMember;
use App\Modules\Order\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SettlementService extends BaseService
{

    /**
     * 获取结算单列表
     * @param array $params {merchantId, operId}
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList(array $params)
    {
        $merchantId = array_get($params, 'merchantId');
        $data = Settlement::where('merchant_id', $merchantId)
            ->orderBy('id', 'desc')
            ->paginate();
        return $data;
    }

    /**
     * 根据ID获取结算单
     * @param $settlementId
     * @return Settlement
     */
    public static function getById($settlementId)
    {
        return Settlement::find($settlementId);
    }

    /**
     * 根据商户ID及结算单获取结算单信息
     * @param $settlementId
     * @param $merchantId
     * @return Settlement
     */
    public static function getByIdAndMerchantId($settlementId, $merchantId)
    {
        return Settlement::where('id', $settlementId)->where('merchant_id', $merchantId)->first();
    }

    /**
     * 获取结算单的订单列表
     * @param $settlementId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getSettlementOrders($settlementId)
    {
        $data = Order::where('settlement_id', $settlementId)
            ->orderBy('id', 'desc')->paginate();
        return $data;
    }

    /**
     * @param $operId
     * @param string $merchantId
     * @param string $status
     * @param string $showAmount
     * @param string $settlementDate
     * @param string $operBizMemberName
     * @param string $operBizMemberMobile
     * @param bool $getWithQuery
     * @return Settlement|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getOperSettlements($operId, $merchantId = '', $status = '', $showAmount = '', $settlementDate = '', $operBizMemberName = '', $operBizMemberMobile = '', $getWithQuery = false)
    {
        $query = DB::table('settlements')
            ->leftJoin('merchants','settlements.merchant_id','=','merchants.id')
            ->leftJoin('oper_biz_members as om','om.code','=','merchants.oper_biz_member_code')
            ->select('settlements.*','merchants.name as merchant_name','om.name as oper_biz_member_name','om.mobile as oper_biz_member_mobile')
            ->where('settlements.oper_id', $operId)
            ->where('settlements.amount','>',0)
            ->orderBy('settlements.id', 'desc');

        if($merchantId){
            $query->where('settlements.merchant_id', $merchantId);
        }
        if($status){
            $query->where('settlements.status', $status);
        }
        if($showAmount){
            $query->where('settlements.amount', '>', 0);
        }
        if($settlementDate){
            $query->whereBetween('settlements.created_at', [$settlementDate[0] . ' 00:00:00', $settlementDate[1] . ' 23:59:59']);
        }
        if($operBizMemberName){
            $query->where('om.name','like', '%'.$operBizMemberName.'%');
        }
        if($operBizMemberMobile){
            $query->where('om.mobile', 'like','%'.$operBizMemberMobile.'%');
        }

        if ($getWithQuery) {
            return $query;
        } else {
            $data = $query->paginate();
            return $data;
        }
    }
}