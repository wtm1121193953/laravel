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
        if($operBizMemberName){
            $code = OperBizMember::where('name',$operBizMemberName)->pluck('code')->first();
            $merchantId = Merchant::where('oper_biz_member_code',$code)->pluck('id')->first();
        }elseif($operBizMemberMobile){
            $code = OperBizMember::where('mobile',$operBizMemberMobile)->pluck('code')->first();
            $merchantId = Merchant::where('oper_biz_member_code',$code)->pluck('id')->first();
        }
        if($settlementDate){
            $starTime = explode(',',$settlementDate);
        }else{
            $starTime = [];
        }
        $data = Settlement::where('oper_id', $operId)
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
            ->when($settlementDate, function (Builder $query) use ($starTime){
                $query->whereBetween('created_at', [$starTime[0] . ' 00:00:00', $starTime[1] . ' 23:59:59']);
            })->orderBy('id', 'desc');

        if ($getWithQuery) {
            return $data;
        } else {
            $data = $data->paginate();
            return $data;
        }
    }
}