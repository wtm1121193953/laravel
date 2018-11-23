<?php

namespace App\Console\Commands;

use App\DataCacheService;
use App\Jobs\ImageMigrationToCOSJob;
use App\Jobs\Schedule\InviteUserStatisticsDailyJob;
use App\Jobs\Schedule\PlatformTradeRecordsDailyJob;
use App\Jobs\Schedule\SettlementAgentPayDaily;

use App\Jobs\Schedule\SettlementDaily;
use App\Jobs\Schedule\SettlementForPlatformWeekly;
use App\Jobs\Schedule\SettlementGenBatch;
use App\Jobs\Schedule\SettlementWeekly;

use App\Jobs\OrderFinishedJob;
use App\Jobs\SettlementAgentPay;
use App\Modules\Dishes\DishesGoods;
use App\Modules\Goods\Goods;
use App\Modules\Invite\InviteChannel;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Invite\InviteUserService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAudit;
use App\Modules\Merchant\MerchantStatisticsService;
use App\Modules\Message\MessageNoticeService;
use App\Modules\Order\Order;
use App\Modules\Order\OrderItem;
use App\Modules\Order\OrderPay;
use App\Modules\Order\OrderService;
use App\Modules\Payment\Payment;
use App\Modules\Payment\PaymentService;
use App\Modules\Settlement\Settlement;
use App\Modules\Settlement\SettlementPlatformKuaiQianBatch;
use App\Modules\Settlement\SettlementPlatformKuaiQianBatchService;
use App\Modules\Sms\SmsService;
use App\Modules\Tps\TpsBind;
use App\Modules\User\User;
use App\Modules\User\UserStatisticsService;
use App\Modules\Wechat\WechatService;
use App\Support\Reapal\ReapalPay;
use App\Support\Utils;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

use App\Jobs\ConsumeQuotaSyncToTpsJob;
use App\Jobs\InviteChannelsUnbindMaker;
use Illuminate\Support\Facades\Storage;

use App\Support\TpsApi;
use App\Jobs\Schedule\OperAndMerchantAndUserStatisticsDailyJob;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $order = Order::find(4703);
        $a = OrderFinishedJob::dispatch($order);
        dd('over');

        $i = 1;
        while (1) {
            $endTime = date('Y-m-d', strtotime("-{$i} day")) . ' 23:59:59';
            $this->info($endTime);
            OperAndMerchantAndUserStatisticsDailyJob::dispatch($endTime);
            if (date('Y-m-d', strtotime("-{$i} day")) <= '2018-04-17') {
                break;
            }
            $i++;
        }
        return;

//        SettlementPlatformKuaiQianBatchService::batchQuery();
//        dd('ok1');
//        SettlementPlatformKuaiQianBatchService::batchSend();
//
//        dd('ok');
//        SettlementGenBatch::dispatch();
//        dd('ok');
//        DataCacheService::delMerchantDetail([29]);
//        dd('hi');
//        $payment =  PaymentService::getDetailById(4);
//        $paymentClassName = '\\App\\Support\\Payment\\'.$payment->class_name;
//        var_dump($paymentClassName);
//        var_dump(class_exists($paymentClassName));
//        /*if(!class_exists($paymentClassName)){
//            throw new BaseResponseException('无法使用该支付方式');
//        }
//        MessageNoticeService::createByRegister('13929492991',80);*/
//        dd(456);

        SettlementDaily::dispatch();
//        dd('hi');
        SettlementForPlatformWeekly::dispatch();
        dd(213);
        PlatformTradeRecordsDailyJob::dispatch();
        dd('ok');
        $param = [
            'title' => 'test_title',
            'body' => 'testBody',
            'order_no' => '1379988876545TestOrderNo',
            'total_fee' => 1,
            'merchantId' => 2,
            'store_name' => 'merchant_name_test',
            'store_phone' =>'13929492991',
            'open_id' => 'owZet4vEP92tzXHIjrICO6_sIts8',
        ];
        if (empty($param['body'])) {
            $param['body'] = $param['title'];
        }
        $reapal = new ReapalPay();
        $result = $reapal->prepay($param);
        dd($result);

        if (empty($result['wxjsapi_str'])) {
            throw new BaseResponseException('微信支付失败');
        }
        $data = DishesGoods::where('id',10014)->get();
        ImageMigrationToCOSJob::dispatch($data,['detail_image']);
        dd(config('cos.cos_url'));
        InviteUserStatisticsDailyJob::dispatch((new Carbon())->subDay());
dd('ok');
        OperAndMerchantAndUserStatisticsDailyJob::dispatch((new Carbon())->subDay()->endOfDay()->format('Y-m-d H:i:s'));
        dd('hi');
//        $data = InviteChannel::where('id','<','10')->pluck('id');
//        var_dump($data);
        InviteChannelsUnbindMaker::dispatch();
        dd('bye');
        $columns = Schema::getColumnListing('wallet_consume_quota_records');
        dd($columns);
        $a = 230;
        $b = 180;
        $val = floor(($a + $b - floor($a / 100) * 100) / 100);
        dd($val);


        SettlementDaily::dispatch();
        dd('hi');
        $orders = Order::all();
        foreach ($orders as $order) {
//            $order->splitting_status = 1;
//            $order->settlement_rate = 20;
//            $order->save();
            $this->info($order->id);
            ConsumeQuotaSyncToTpsJob::dispatch($order);
        }
        dd('hi');
        $TpsBind = new TpsBind();
        $TpsBind->origin_type = 1;
        $TpsBind->origin_id = 128;
        $TpsBind->tps_uid = 1;
        $TpsBind->tps_account = 'test_data_';
        $TpsBind->save();
        dd('saved');

//        SettlementAgentPay::dispatch([1]);
//        dd(1234);
        $orders = Order::all();
        foreach ($orders as $order) {
//            $order->splitting_status = 1;
//            $order->settlement_rate = 20;
//            $order->save();
            $this->info($order->id);
            OrderFinishedJob::dispatch($order)->onQueue('order:finished');
        }
        dd('ok');

        $url = 'http://yunjipin-o2o.com/storage/miniprogram/app_code/_123_375.jpg';
        WechatService::addNameToAppCode($url, '招牌名称哈哈哈哈哈哈哈哈哈哈');
        dd(pathinfo($url, PATHINFO_BASENAME));

        $user = User::where('mobile', '13333333333')->first();
        dd($user);

        SmsService::sendBuySuccessNotify('O20180619165606342090');
        dd();
        $orderItems = OrderItem::where('order_id', 102)
            ->select('verify_code')
            ->get()
            ->pluck('verify_code')
            ->toArray();
        $verifyCode = implode(',', $orderItems);
        dd($verifyCode);
        return;
    }

    /**
     * 补单 O20180601132436766626
     * 交易单号 4200000114201806019314457579
     * 支付金额 74.00
     */
    private function repayO20180601132436766626()
    {
        $orderNo = 'O20180601132436766626';
        $transactionId = '4200000114201806019314457579';
        $totalFee = 7400;
        // 处理订单支付成功逻辑
        $order = Order::where('order_no', $orderNo)->firstOrFail();

        if($order->status === Order::STATUS_UN_PAY
            || $order->status === Order::STATUS_CANCEL
            || $order->status === Order::STATUS_CLOSED
        ){
            $order->pay_time = Carbon::now(); // 更新支付时间为当前时间
            if($order->type == Order::TYPE_SCAN_QRCODE_PAY){
                // 如果是扫码付款, 直接改变订单状态为已完成
                $order->status = Order::STATUS_FINISHED;
                $order->finish_time = Carbon::now();
                $order->save();
            }else {
                $order->status = Order::STATUS_PAID;
                $order->save();
            }

            // 添加商品已售数量
            Goods::where('id', $order->goods_id)->increment('sell_number');

            // 生成核销码, 线上需要放到支付成功通知中
            for ($i = 0; $i < $order->buy_number; $i ++){
                $orderItem = new OrderItem();
                $orderItem->oper_id = $order->oper_id;
                $orderItem->merchant_id = $order->merchant_id;
                $orderItem->order_id = $order->id;
                $orderItem->verify_code = OrderItem::createVerifyCode($order->merchant_id);
                $orderItem->status = 1;
                $orderItem->save();
            }
            // 生成订单支付记录
            $orderPay = new OrderPay();
            $orderPay->order_id = $order->id;
            $orderPay->order_no = $orderNo;
            $orderPay->transaction_no = $transactionId;
            $orderPay->amount = $totalFee * 1.0 / 100;
            $orderPay->save();

            // 支付成功, 如果用户没有被邀请过, 将用户的邀请人设置为当前商户
            $userId = $order->user_id;
            if( empty( InviteUserRecord::where('user_id', $userId)->first() ) ){
                $merchantId = $order->merchant_id;
                $merchant = Merchant::findOrFail($merchantId);
                $inviteChannel = InviteChannelService::getByOriginInfo($merchantId, InviteChannel::ORIGIN_TYPE_MERCHANT, $merchant->oper_id);
                InviteUserService::bindInviter($userId, $inviteChannel);
            }

            return true;
        }else if($order->status == Order::STATUS_PAID){
            // 已经支付成功了
            return true;
        }else if($order->status == Order::STATUS_REFUNDING
            || $order->status === Order::STATUS_REFUNDED
            || $order->status === Order::STATUS_FINISHED
        ){
            // 订单已退款或已完成
            return true;
        }
        return false;
    }

    private function remedySettlementsOf20180521and20180528()
    {
        // 执行 20180521 的结算
        $date = '2018-05-21';
        $start = Carbon::createFromFormat('Y-m-d', $date)->subWeek()->startOfWeek();
        $end = Carbon::createFromFormat('Y-m-d', $date)->subWeek()->endOfWeek();
        $this->settlementForDate($date, $start, $end);
        // 执行 20180528 的结算
        $date = '2018-05-28';
        $start = Carbon::createFromFormat('Y-m-d', $date)->subWeek()->startOfWeek();
        $end = Carbon::createFromFormat('Y-m-d', $date)->subWeek()->endOfWeek();
        $this->settlementForDate($date, $start, $end);
    }

    private function settlementForDate($date, $start, $end)
    {
        Merchant::where('settlement_cycle_type', Merchant::SETTLE_WEEKLY)
            ->where('oper_id', '>', 0)
            ->chunk(100, function($merchants) use ($date, $start, $end){
                $merchants->each(function ($item) use ($date, $start, $end){

                    $merchant = $item;
                    // 判断该周期是否已结算过, 若结算过则不再重复结算
                    $settlement = Settlement::where('merchant_id', $merchant->id)
                        ->where('settlement_date', $date)
                        ->first();
                    if($settlement){
                        Log::info('该周期已结算,跳过结算', [
                            'merchantId' => $merchant->id,
                            'date' => $date,
                            'start' => $start,
                            'end' => $end,
                        ]);
                        return ;
                    }
                    // 先生成结算单, 以便于结算时在订单中保存结算信息
                    $settlement = new Settlement();
                    $settlement->oper_id = $merchant->oper_id;
                    $settlement->merchant_id = $merchant->id;
                    $settlement->settlement_date = $date;
                    $settlement->start_date = $start;
                    $settlement->end_date = $end;
                    $settlement->settlement_cycle_type = $merchant->settlement_cycle_type;
                    $settlement->settlement_rate = $merchant->settlement_rate;
                    $settlement->bank_open_name = $merchant->bank_open_name;
                    $settlement->bank_card_no = $merchant->bank_card_no;
                    $settlement->sub_bank_name = $merchant->sub_bank_name;
                    $settlement->bank_open_address = $merchant->bank_open_address;
                    $settlement->invoice_title = $merchant->invoice_title;
                    $settlement->invoice_no = $merchant->invoice_no;
                    $settlement->amount = 0;
                    $settlement->charge_amount = 0;
                    $settlement->real_amount = 0;
                    $settlement->save();

                    // 查询商家结算周期内的所有订单并统计
                    Order::where('merchant_id', $merchant->id)
                        ->where('status', Order::STATUS_FINISHED)
                        ->whereBetween('finish_time', [$start, $end])
                        ->chunk(1000, function (Collection $orders) use ($merchant, $settlement){
                            $orders->each(function($item) use ($merchant, $settlement){

                                $settlement->amount += $item->pay_price;

                                $item->settlement_status = 2;
                                $item->settlement_id = $settlement->id;
                                $item->save();
                            });
                        });

                    $settlement->charge_amount = $settlement->amount * 1.0 * $settlement->settlement_rate / 100;
                    $settlement->real_amount = $settlement->amount - $settlement->charge_amount;
                    $settlement->save();
                });
            });
    }
}
