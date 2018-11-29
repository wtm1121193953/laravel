<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/25
 * Time: 17:03
 */

namespace App\Modules\Settlement;


use App\BaseService;
use App\Exceptions\ParamInvalidException;
use App\Modules\Cs\CsMerchant;
use App\Modules\Merchant\Merchant;
use App\Modules\Order\Order;
use App\Support\AgentPay\KuaiQian;
use http\Exception\BadMessageException;
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
        3 => '打款成功',
        4 => '打款失败',
        5 => '已重新打款',            // changed by Jerry 新增状态
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
        //展示所有结算周期的结算单
        if(!empty($params['isAutoSettlement'])){
            if (!empty($params['settlementCycleType'])) {
                $query->where('settlement_cycle_type',$params['settlementCycleType']);
            }
        }else{
            //展示T+1人工以及周结的结算单
            if (!empty($params['settlementCycleType'])) {
                if($params['settlementCycleType'] == SettlementPlatform::SETTLE_WEEKLY){
                    $query->where('settlement_cycle_type',SettlementPlatform::SETTLE_WEEKLY);
                }elseif ($params['settlementCycleType'] == SettlementPlatform::SETTLE_DAY_ADD_ONE){
                    $query->where('settlement_cycle_type', SettlementPlatform::SETTLE_DAY_ADD_ONE);
                }
            }else{
                $query->whereIn('settlement_cycle_type',[SettlementPlatform::SETTLE_WEEKLY, SettlementPlatform::SETTLE_DAY_ADD_ONE]);
            }
        }
        if (!empty($params['merchantType'])) {
            $query->where('merchant_type',$params['merchantType']);
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

        if (!empty($params['status'])) {
            if (!is_array($params['status'])) {
                $params['status'] = explode(',',$params['status']);
            }
            $query->whereIn('status', $params['status']);
        }

        if($params['merchantType']==SettlementPlatform::MERCHANT_TYPE_CS){
            $query->with('cs_merchant:id,name');

        }else{
            $query->with('merchant:id,name');
        }
        $query->with('oper:id,name')
            ->orderBy('id', 'desc');

        if ($return_query) {
            return  $query;
        }
        $data = $query->paginate();

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
     * @param Merchant|CsMerchant $merchant
     * @param Carbon $date
     * @return bool [bool]
     * @throws \Exception
     */
    public static function settlement( $merchant, $date)
    {
        if($merchant instanceof Merchant){
            $merchantType = Order::MERCHANT_TYPE_NORMAL;
        }elseif ($merchant instanceof CsMerchant){
            $merchantType = Order::MERCHANT_TYPE_SUPERMARKET;
        }else{
            throw new \Exception('暂无该类型用户');
        }
        $query = Order::where('merchant_id', $merchant->id)
            ->where('settlement_status', Order::SETTLEMENT_STATUS_NO )
            ->where('pay_target_type', Order::PAY_TARGET_TYPE_PLATFORM)
            ->where('status', Order::STATUS_FINISHED )
            ->where('finish_time','<=', $date->endOfDay())
            ->where('merchant_type',$merchantType);
        // 统计所有需结算金额
        $sum = $query->sum('pay_price');

        //获得结算周期时间
        $start_date = $query->min('finish_time');
        //如果该商户无订单，跳过结算
        if(empty($start_date)){
            Log::info('商家每日无订单，跳过结算', [
                'merchantId' => $merchant->id,
                'merchantType'  =>  $merchantType,
                'date' => $date,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            return true;
        }
        $end_date = $date;

        if( $sum<100 ){

            //从第一个订单起7天内如果订单金额还不满100，直接强制生成结算单
            $start = strtotime($start_date);
            $now = strtotime($date->endOfDay())?? strtotime("now");
            $diffDay = ($now-$start)/86400;

            if($diffDay >= 7){
                Log::info('商家结算单超过七天，且总金额小于100，直接强制生成结算单', [
                    'merchantId' => $merchant->id,
                    'merchantType'  =>  $merchantType,
                    'start_date' => $start_date,
                    'start' => $start,
                    'diffDay' => $diffDay,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }else{
                Log::info('商家每日结算时订单金额小于100，跳过结算', [
                    'merchantId' => $merchant->id,
                    'merchantType'  =>  $merchantType,
                    'start_date' => $start_date,
                    'start' => $start,
                    'diffDay' => $diffDay,
                    'now' => $now,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return true;
            }
        }

        // 生成结算单，方便之后结算订单中保存结算信息
        $settlementNum = self::genSettlementNo(10);
        if( !$settlementNum ) {
            throw new \Exception('结算单号生成失败');
        }

        $type = ($merchant->settlement_cycle_type == Merchant::SETTLE_DAILY_AUTO) ? SettlementPlatform::TYPE_AGENT:SettlementPlatform::TYPE_DEFAULT;


        // 开启事务
        DB::beginTransaction();
        try{
            $settlementPlatform = new SettlementPlatform();
            $settlementPlatform->oper_id = $merchant->oper_id;
            $settlementPlatform->merchant_id = $merchant->id;
            $settlementPlatform->start_date = $start_date;
            $settlementPlatform->end_date = $end_date;
            $settlementPlatform->type = $type;
            $settlementPlatform->merchant_type = $merchantType;
            $settlementPlatform->settlement_cycle_type = $merchant->settlement_cycle_type;
            $settlementPlatform->settlement_no = $settlementNum;
            $settlementPlatform->settlement_rate = $merchant->settlement_rate;
            $settlementPlatform->bank_open_name = $merchant->bank_open_name;
            $settlementPlatform->bank_card_no = $merchant->bank_card_no;
            $settlementPlatform->bank_card_type = $merchant->bank_card_type;
            $settlementPlatform->sub_bank_name = $merchant->bank_name .'|' . $merchant->sub_bank_name;
            $settlementPlatform->bank_open_address = $merchant->bank_province . ',' . $merchant->bank_city . ',' . $merchant->bank_area .'|' .$merchant->bank_open_address;
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
     * 处理每周之前结算细节入库
     * @Author   Jerry
     * @DateTime 2018-08-24
     * @param $merchant
     * @param Carbon $date
     * @return bool [bool]
     * @throws \Exception
     */
    public static function settlementWeekly( $merchant, $date)
    {
        $query = Order::where('merchant_id', $merchant->id)
            ->where('settlement_status', Order::SETTLEMENT_STATUS_NO )
            ->where('pay_target_type', Order::PAY_TARGET_TYPE_PLATFORM)
            ->where('status', Order::STATUS_FINISHED )
            ->where('finish_time','<=', $date);

        //获得结算周期时间
        $start_date = $query->min('finish_time');
        //如果该商户无订单，跳过结算
        if(empty($start_date)){
            Log::info('商家上周无订单，跳过结算', [
                'merchantId' => $merchant->id,
                'date' => $date,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            return true;
        }
        $end_date = $date;

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
            $settlementPlatform->type = SettlementPlatform::TYPE_DEFAULT;
            $settlementPlatform->settlement_cycle_type = $merchant->settlement_cycle_type;
            $settlementPlatform->settlement_no = $settlementNum;
            $settlementPlatform->settlement_rate = $merchant->settlement_rate;
            $settlementPlatform->bank_open_name = $merchant->bank_open_name;
            $settlementPlatform->bank_card_no = $merchant->bank_card_no;
            $settlementPlatform->bank_card_type = $merchant->bank_card_type;
            $settlementPlatform->sub_bank_name = $merchant->bank_name .'|' . $merchant->sub_bank_name;
            $settlementPlatform->bank_open_address = $merchant->bank_province . ',' . $merchant->bank_city . ',' . $merchant->bank_area .'|' .$merchant->bank_open_address;
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
            [
                'status' => SettlementPlatform::STATUS_PAID,
                'reason' => ''
            ]
        );
        return $data;
    }

    /**
     * 生成块钱批次
     */
    public static function autoGenBatch() {

        $batch_total = 100; //最多生成的批次数
        $settlement_total = 1000;//一个批次结算单数量

        $kuaiqian = new KuaiQian();
        for ($i=1; $i<=$batch_total; $i++) {
            $settlement_platform = SettlementPlatform::where('pay_batch_no','')
                ->where('settlement_cycle_type',SettlementPlatform::SETTLE_MONTHLY) //T+1(自动) 原月结数据
                ->where('status',SettlementPlatform::STATUS_UN_PAY)
                ->where('real_amount','>',0)
                ->limit($settlement_total)
                ->get()
            ;

            $cnt = $settlement_platform->count();
            if (empty($cnt)) {
                break;
            }

            // 新增，根据商户类型进行每日生成2条数据
            $settlementPlatformGroup = $settlement_platform->groupBy('merchant_type');
            foreach ($settlementPlatformGroup as $k=>$v){
                if(empty($settlement_platform->count())){
                    continue;
                }
                $settlement_platform = $v;
                DB::beginTransaction();
                try {
                    $batch_no = SettlementPlatformKuaiQianBatchService::genBatchNo();

                    $rs = $kuaiqian->genXmlSend($settlement_platform, $batch_no);
                    if (empty($rs) || empty($rs['settlement_platform_ids'])) {
                        continue;
                    }



                    $settlement_platform_ids = $rs['settlement_platform_ids'];
                    $data_send = $rs['originalData'];

                    $m = new SettlementPlatformKuaiQianBatch();
                    $m->type = SettlementPlatformKuaiQianBatch::TYPE_AUTO;
                    $m->merchant_type = $k;
                    $m->batch_no = $batch_no;
                    $m->settlement_platfrom_ids = implode(',',$settlement_platform_ids);
                    $m->total = count($settlement_platform_ids);
                    $m->amount = $rs['amount']/100;
                    $m->data_send = $data_send;
                    $m->data_receive = '';
                    $m->data_query = '';
                    $m->create_date = date('Y-m-d');
                    $m->send_time = date('Y-m-d H:i:s');

                    $m->save();

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('生成结算批次数据库错误', [
                        'message' => $e->getMessage(),
                        'data' => $e
                    ]);
                    throw $e;
                }
            }


        }
    }


    /**
     * 重新打款
     * @param $settlement_id 结算单ID
     * @return SettlementPlatformKuaiQianBatch
     * @throws \Exception
     */
    public static function genBatchAgain($settlement_id)
    {
        //重新打款更新商户最新银行卡信息
        $settlement_info = SettlementPlatform::findOrFail($settlement_id);
        if ($settlement_info->status!= SettlementPlatform::STATUS_FAIL) {
            throw new ParamInvalidException('只有打款失败的结算单可以重新打款');
        }
        $merchant = Merchant::findOrFail($settlement_info->merchant_id);

        $settlement_info->bank_open_name = $merchant->bank_open_name;
        $settlement_info->bank_card_no = $merchant->bank_card_no;
        $settlement_info->bank_card_type = $merchant->bank_card_type;
        $settlement_info->sub_bank_name = $merchant->bank_name .'|' . $merchant->sub_bank_name;
        $settlement_info->bank_open_address = $merchant->bank_province . ',' . $merchant->bank_city . ',' . $merchant->bank_area .'|' .$merchant->bank_open_address;
        $settlement_info->invoice_title = $merchant->invoice_title;
        $settlement_info->invoice_no = $merchant->invoice_no;
        $settlement_info->save();

        $settlement_platform = SettlementPlatform::where('id',$settlement_id)
            ->where('settlement_cycle_type',SettlementPlatform::SETTLE_MONTHLY) //T+1(自动) 原月结数据
            ->where('real_amount','>',0)
            ->get()
        ;

        $cnt = $settlement_platform->count();
        if (empty($cnt)) {
            throw new ParamInvalidException('结算单信息有误');
        }

        $kuaiqian = new KuaiQian();
        DB::beginTransaction();
        try {
            $batch_no = SettlementPlatformKuaiQianBatchService::genBatchNo();

            $rs = $kuaiqian->genXmlSend($settlement_platform, $batch_no, true);
            if (empty($rs) || empty($rs['settlement_platform_ids'])) {
                throw new ParamInvalidException('生成报文错误');
            }

            $settlement_platform_ids = $rs['settlement_platform_ids'];
            $data_send = $rs['originalData'];

            $m = new SettlementPlatformKuaiQianBatch();
            $m->type = SettlementPlatformKuaiQianBatch::TYPE_RE_PAY;
            $m->batch_no = $batch_no;
            $m->settlement_platfrom_ids = implode(',',$settlement_platform_ids);
            $m->total = count($settlement_platform_ids);
            $m->amount = $rs['amount']/100;
            $m->data_send = $data_send;
            $m->data_receive = '';
            $m->data_query = '';
            $m->create_date = date('Y-m-d');
            $m->send_time = date('Y-m-d H:i:s');

            $m->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('生成结算批次数据库错误', [
                'message' => $e->getMessage(),
                'data' => $e
            ]);
            throw $e;
        }

        SettlementPlatformKuaiQianBatchService::sendByBatchNo($batch_no);
        return $m;
    }

}