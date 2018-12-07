<?php

namespace App\Modules\Platform;

use App\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PlatformTradeRecordService extends BaseService
{
    //
    /**
     * 查询订单列表
     * @param array $params
     * @param bool $getWithQuery
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Builder|mixed
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
            ->when($params['merchant_type'],function (Builder $query) use ($params) {
                $query->where('merchant_type',$params['merchant_type']);
            })
            ->when($params['user_id'],function (Builder $query) use ($params) {
                $query->whereHas('user',function($q) use ($params) {
                    $q->where('user_id', "{$params['user_id']}");
                });
            })
            ->when($params['startTime'] && $params['endTime'],function (Builder $query) use ($params) {
                $query->where('trade_time','>=',"{$params['startTime']} 00:00:00");
                $query->where('trade_time','<=',"{$params['endTime']} 23:59:59");
            })
        ;

        $query->with('oper:id,name');
        $query->with('merchant:id,name');
        $query->with('csMerchant:id,name');


        $query->orderBy('trade_time', 'desc');

        if ($getWithQuery) {
            return $query;
        }

        $data = $query->paginate();


        $data->each(function ($item) {
            if ($item->type == 2) {
                $item->trade_amount = '-' . $item->trade_amount;
            }
        });

        return $data;
    }

    public static function daily($date='')
    {
        if (empty($date)) {
            $date = date('Y-m-d',strtotime('-1 day'));
        } else {
            $date = date('Y-m-d',strtotime($date));
        }


        $query = PlatformTradeRecord::select(DB::raw('pay_id,type,count(*) c,sum(trade_amount) s'))
            ->where('trade_time','>=',$date . ' 00:00:00')
            ->where('trade_time','<=',$date . ' 23:59:59')
            ->groupBy('pay_id','type')
        ;

        $data = $query->get();
        $format = [];
        if (!empty($data)) {

            foreach ($data as $d) {

                if ($d->type == 1) {
                    $format[$date][$d->pay_id]['pay_amount'] = $d->s;
                    $format[$date][$d->pay_id]['pay_count'] = $d->c;
                } else {
                    $format[$date][$d->pay_id]['refund_amount'] = $d->s;
                    $format[$date][$d->pay_id]['refund_count'] = $d->c;
                }

            }

            foreach ($format as $k1 => $v1) {
                foreach ($v1 as $k2=>$v2) {
                    $where['sum_date'] = $k1;
                    $where['pay_id'] = $k2;

                    $row['sum_date'] = $k1;
                    $row['pay_id'] = $k2;
                    $row['pay_amount'] = $v2['pay_amount'] ?? 0;
                    $row['pay_count'] = $v2['pay_count'] ?? 0;
                    $row['refund_amount'] = $v2['refund_amount']?? 0;
                    $row['refund_count'] = $v2['refund_count']?? 0;

                    //dd($row);
                    PlatformTradeRecordsDaily::updateOrCreate($where,$row);
                }
            }
        }
    }
}