<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/25
 * Time: 17:03
 */

namespace App\Modules\Settlement;


use App\BaseService;
use App\Modules\Order\Order;

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
     * 获取运营中心的财务列表
     * @param $operId
     * @param string $keyword
     * @param bool $getWithQuery
     * @return Settlement|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getOperInviteChannels($operId, $keyword = '', $getWithQuery = false)
    {
        $query = Settlement::where('origin_id', $operId)
            ->when('keyword', function (Builder $query) use ($keyword){
                $query->where('name', 'like', "%$keyword%");
            })
            ->where('amount', '>', 0)
            ->orderBy('id', 'desc');
        if ($getWithQuery) {
            return $query;
        } else {
            $data = $query->paginate();
            return $data;
        }
    }
}