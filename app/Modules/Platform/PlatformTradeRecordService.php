<?php

namespace App\Modules\Platform;

use App\BaseService;
use Illuminate\Database\Eloquent\Builder;

class PlatformTradeRecordService extends BaseService
{
    //
    /**
     * 查询订单列表
     * @param array $params
     * @param bool $getWithQuery
     * @return Order|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList(array $params, $getWithQuery = false)
    {
        $query = PlatformTradeRecord::query()
            ->when($params['order_no'],function (Builder $query) use ($params) {
                $query->where('order_no','like',"%{$params['order_no']}%");
            })
            ->when($params['trade_no'],function (Builder $query) use ($params) {
                $query->where('trade_no','like',"%{$params['trade_no']}%");
            })
            ->when($params['oper_id'],function (Builder $query) use ($params) {
                $query->where('oper_id','=',"{$params['oper_id']}");
            })
            ->when($params['merchant_id'],function (Builder $query) use ($params) {
                $query->where('merchant_id','=',"{$params['merchant_id']}");
            })
            ->when($params['mobile'],function (Builder $query) use ($params) {
                $query->whereHas('user',function($q) use ($params) {
                    $q->where('mobile', 'like', "%{$params['mobile']}%");
                });
            })
            ->when($params['startTime'] && $params['endTime'],function (Builder $query) use ($params) {
                $query->where('trade_time','>=',"{$params['startTime']} 00:00:00");
                $query->where('trade_time','<=',"{$params['endTime']} 23:59:59");
            })
        ;

        $query->with('oper:id,name');
        $query->with('merchant:id,name');
        $query->with('user:id,mobile');


        $query->orderBy('trade_time', 'desc');

        if ($getWithQuery) {
            return $query;
        }

        $data = $query->paginate();


        return $data;
    }
}