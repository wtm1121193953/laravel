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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;


class SettlementPlatformService extends BaseService
{

    /**
     * @var array
     */
    public static $status_vals = [
        1 => '未打款',
        2 => '打款中',
        3 => '已打款',
        4 => '已到账',
        5 => '打款失败',            // changed by Jerry 新增状态
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
     * 通过结算单号获取列表
     * @param $settlementNo
     * @return SettlementPlatform
     */
    public static function getBySettlementNo($settlementNo)
    {
        $data = SettlementPlatform::where('settlement_no', $settlementNo)->first();
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
        $query = SettlementPlatform::where('id', '>', 0);
        if (!empty($params['merchant_name'])) {
            $query->whereHas('merchant',function($q) use ($params) {
                $q->where('name', 'like', "%{$params['merchant_name']}%");
            });
        }

        if (!empty($params['merchant_id'])) {
            $query->where('merchant_id','=', $params['merchant_id']);
        }

        if (!empty($params['startDate'])) {
            $query->where('created_at', '>=', Carbon::createFromFormat('Y-m-d',$params['startDate'])->startOfDay());
        }

        if (!empty($params['endDate'])) {
            $query->where('created_at', '<=', Carbon::createFromFormat('Y-m-d',$params['endDate'])->endOfDay());
        }

        if (is_array($params['status']) || $params['status'] instanceof Collection) {
            $query->whereIn('status', $params['status']);
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
            if($item->satatus==5) $item->status_val .= $item->reason;
        });
        return $data;
    }

    /**
     * 生成随机结算单号
     * @param int $retry
     * @return string
     *
     */
    public static function genSettlementNo($retry = 100)
    {
        if($retry == 0){
            return false;
        }
        $orderNo = 'PB'.date('ymd') . explode(' ', microtime())[0] * 1000000 . rand(1000, 9999);
        if(SettlementPlatform::where('settlement_no', $orderNo)->first())
        {
            $orderNo = self::genSettlementNo(--$retry);
        }
        return $orderNo;
    }

    /**
     * 处理每日结算细节入库
     * @Author   Jerry
     * @DateTime 2018-08-24
     * @param $merchant
     * @param Carbon $date
     * @param $type
     * @return bool [bool]
     * @throws \Exception
     */
    public static function settlement( $merchant, $date, $type )
    {
        $pay_type = [];
        if($type == SettlementPlatform::TYPE_DEFAULT){
            $pay_type = [Order::PAY_TYPE_WECHAT];
        }elseif ($type == SettlementPlatform::TYPE_AGENT){
            $pay_type = [Order::PAY_TYPE_REAPAL];
        }
        $query = Order::where('merchant_id', $merchant->id)
            ->where('settlement_status', Order::SETTLEMENT_STATUS_NO )
            ->where('pay_target_type', Order::PAY_TARGET_TYPE_PLATFORM)
            ->where('status', Order::STATUS_FINISHED )
            ->whereIn('type', $pay_type)
            ->where('finish_time','<=', $date->endOfDay());
        // 统计所有需结算金额
        $sum = $query->sum('pay_price');

        if( $sum<100 ){
            Log::info('商家每日结算时订单金额小于100，跳过结算', [
                'merchantId' => $merchant->id,
                'date' => $date,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            return true;
        }
        //获得结算周期时间
        $start_date = $query->min('pay_time');
        $end_date = $query->max('pay_time');

        // 生成结算单，方便之后结算订单中保存结算信息
        $settlementNum = self::genSettlementNo(10);
        if( !$settlementNum ) {
            throw new \Exception('结算单号生成失败');
        }

        // 开启事务
        DB::beginTransaction();
        try{
            $settlementPlatform = new SettlementPlatform();
            $settlementPlatform->oper_id = $merchant->oper_id;
            $settlementPlatform->merchant_id = $merchant->id;
            $settlementPlatform->start_date = $start_date;
            $settlementPlatform->end_date = $end_date;
            $settlementPlatform->type = $type;
            $settlementPlatform->settlement_no = $settlementNum;
            $settlementPlatform->settlement_rate = $merchant->settlement_rate;
            $settlementPlatform->bank_open_name = $merchant->bank_open_name;
            $settlementPlatform->bank_card_no = $merchant->bank_card_no;
            $settlementPlatform->bank_card_type = $merchant->bank_card_type;
            $settlementPlatform->sub_bank_name = $merchant->bank_name .'|' . $merchant->sub_bank_name;
            $settlementPlatform->bank_open_address = $merchant->bank_province . $merchant->bank_city . $merchant->bank_area .'|' .$merchant->bank_open_address;
            $settlementPlatform->invoice_title = $merchant->invoice_title;
            $settlementPlatform->invoice_no = $merchant->invoice_no;
            $settlementPlatform->amount = 0;
            $settlementPlatform->charge_amount = 0;
            $settlementPlatform->real_amount = 0;
            $settlementPlatform->save();

            // 统计订单总金额与改变每笔订单状态
            $list = $query->select('id', 'settlement_charge_amount', 'settlement_real_amount', 'settlement_status', 'settlement_id', 'pay_price','settlement_rate')->get();
            $list->each( function(Order $item ) use ( $merchant, $settlementPlatform ){
                $item->settlement_charge_amount = $item->pay_price * $item->settlement_rate / 100;  // 手续费
                $item->settlement_real_amount = $item->pay_price - $item->settlement_charge_amount;   // 货款
                $item->settlement_status = Order::SETTLEMENT_STATUS_FINISHED;
                $item->settlement_id = $settlementPlatform->id;
                $item->save();

                // 结算实收金额
                $settlementPlatform->amount += $item->pay_price;
                $settlementPlatform->charge_amount += $item->settlement_charge_amount;
                $settlementPlatform->real_amount += $item->settlement_real_amount;
                $settlementPlatform->save();
            });
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 根据商户ID及结算单获取结算单信息
     * @param $settlementId
     * @param $merchantId
     * @return SettlementPlatform
     */
    public static function getByIdAndMerchantId($settlementId, $merchantId)
    {
        return SettlementPlatform::where('id', $settlementId)->where('merchant_id', $merchantId)->first();
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
     * 通过id获取结算单更新状态
     * @param $id
     * @return bool
     */
    public static function getByIdModifyStatus($id)
    {
        $data = SettlementPlatform::where('id', $id)->update(
            ['status' => SettlementPlatform::STATUS_PAID]
        );
        return $data;
    }

}