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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Boolean;
use tests\Mockery\Adapter\Phpunit\EmptyTestCase;

class SettlementPlatformService extends BaseService
{

    /**
     * @var array
     */
    public static $status_vals = [
        1 => '未打款',
        2 => '已打款',
        3 => '已到账',
        4 => '打款失败',
    ];
    /**
     * 获取结算单列表
     * @param array $params {merchantId, operId}
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList(array $params)
    {
        $merchantId = array_get($params, 'merchantId');
        $data = SettlementPlatform::where('merchant_id', $merchantId)
            ->orderBy('id', 'desc')
            ->paginate();
        return $data;
    }

    /**
     * SAAS获取结算单列表【新】
     * @param array $params
     * @param bool $return_query
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getListForSaas(array $params = [],bool $return_query = false)
    {
        //DB::enableQueryLog();
        $query = SettlementPlatform::where('id', '>', 0);
        if (!empty($params['merchant_name'])) {
            $query->whereHas('merchant',function($q) use ($params) {
                $q->where('name', 'like', "%{$params['merchant_name']}%");
            });
        }

        if (!empty($params['merchant_id'])) {
            $query->where('merchant_id','=', $params['merchant_id']);
        }

        if (!empty($params['startDate']) && !empty($params['endDate'])) {
            $query->where('date', '>=', $params['startDate']);
            $query->where('date', '<=', $params['endDate']);
        }

        if (is_array($params['status']) || $params['status'] instanceof Collection) {
            $query->whereIn('status', $params['status']);
        }

        if ($params['show_zero'] == 'false') {
            $query->where('real_amount','>', 0);
        }

        $query->with('merchant:id,name')
            ->with('oper:id,name')
            ->orderBy('id', 'desc');
        if ($return_query) {
            return  $query;
        }
        $data = $query->paginate();

        $data->each(function ($item) {
            $item->status_val = self::$status_vals[$item->status];
        });
        //dd(DB::getQueryLog());
        return $data;
    }


}