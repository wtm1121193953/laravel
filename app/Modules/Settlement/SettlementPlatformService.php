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

    /**
     * 处理每日结算细节入库
     * @Author   Jerry
     * @DateTime 2018-08-24
     * @param    [type]
     * @param    [type]
     * @param    [type]
     * @return   [type]
     */
    public static function settlement( $merchant, $start, $end )
    {
        // 生成结算单，方便之后结算订单中保存结算信息
        $saveData= [
            'open_id'           => $merchant->oper_id,
            'merchant_id'       => $merchant->merchantId,
            'settlement_date'   => Carbon::now(),
            'start_date'        => $start,
            'end_date'          => $end,
            'settlement_cycle_type' => $merchant->settlement_cycle_type,
            'settlement_rate'   => $merchant->settlement_rate,
            'bank_open_name'    => $merchant->bank_open_name,
            'bank_card_no'      => $merchant->bank_card_no,
            'sub_bank_name'     => $merchant->sub_bank_name,
            'bank_open_address' => $merchant->bank_open_address,
            'invoice_title'     => $merchant->invoice_title,
            'invoice_no'        => $merchant->invoice_no,
            'amount'            => 0,
            'charge_amount'     => 0,
            'real_amount'       => 0,
        ];
        
        $SettlementPlatform = new SettlementPlatform( $saveData );
        // 开启事务
        DB::beginTransaction();
        try{
            $SettlementPlatform->save();
            // 统计订单总金额与改变每笔订单状态
            Order::where('merchant_id', $merchant->id)
                ->where('settlement_status', Order::SETTLEMENT_STATUS_NO )
                ->where('', Order::STATUS_FINISHED )
                ->whereBetween('finish_time', [$start, $end])
                ->chunk(1000, function( Collection $orders ) use( $merchant, $SettlementPlatform ){
                    $orders->each( function( $item) use ( $merchant, $SettlementPlatform ){
                        $SettlementPlatform->amount += $item->pay_price;

                        $item->settlement_status = Order::SETTLEMENT_STATUS_FINISHED;
                        $item->settlement_id = $SettlementPlatform->id;
                        $item->save();
                    });
                });

            $SettlementPlatform->charge_amount = self::coutChargeAmount( $SettlementPlatform );
            $SettlementPlatform->real_amount= self::countRealAmount( $SettlementPlatform );
            $SettlementPlatform->save();
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * 结算手续费
     * @Author   Jerry
     * @DateTime 2018-08-23
     * @param    [obj] $settlement
     * @return   [float]
     */
    public function coutChargeAmount( SettlementPlatform $settlement )
    {
        return $settlement->charge_amount = $settlement->amount * 1.0 * $settlement->settlement_rate / 100;
    }

    /**
     * 结算商家实际收到的金额
     * @Author   Jerry
     * @DateTime 2018-08-23
     * @param    SettlementPlatform $settlement
     * @return   [float]
     */
    public function countRealAmount( SettlementPlatform $settlement )
    {
        return $settlement->amount - $settlement->charge_amount;
    }



}