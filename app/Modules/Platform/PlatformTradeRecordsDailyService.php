<?php

namespace App\Modules\Platform;

use App\BaseService;
use Illuminate\Database\Eloquent\Builder;

class PlatformTradeRecordsDailyService extends BaseService
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
        $query = PlatformTradeRecordsDaily::query()
            ->when($params['startTime'] && $params['endTime'],function (Builder $query) use ($params) {
                $query->where('sum_date','>=',"{$params['startTime']}");
                $query->where('sum_date','<=',"{$params['endTime']}");
            })
        ;

        $query->orderBy('sum_date', 'desc');

        if ($getWithQuery) {
            return $query;
        }

        $data = $query->paginate();

        $data->each(function ($item) {

            $item->pays = "{$item->pay_amount}元/{$item->pay_count}笔";
            $item->refunds = "{$item->refund_amount}元/{$item->refund_count}笔";
            $item->income = ($item->pay_amount - $item->refund_amount) . '元';
        });

        return $data;
    }
}
