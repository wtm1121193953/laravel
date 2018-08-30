<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/15
 * Time: 16:24
 */

namespace App\Http\Controllers;


use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\NoPermissionException;
use App\Exceptions\ParamInvalidException;
use App\Modules\Log\LogDbService;
use App\Modules\Log\LogOrderNotifyReapal;
use App\Modules\Oper\OperMiniprogramService;
use App\Modules\Order\Order;
use App\Modules\Order\OrderService;
use App\Modules\Wechat\MiniprogramScene;
use App\Modules\Wechat\WechatService;
use App\Result;
use Exception;
use Illuminate\Support\Facades\App;

class PayController extends Controller
{

    /**
     * 小程序支付跳转H5页面
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function miniprogramPayBridgeByH5()
    {

        try{
            $targetOperId = request('targetOperId');
            if(empty($targetOperId)) throw new BaseResponseException('targetOperId不能为空');
            $orderNo = request('orderNo');
            if(empty($orderNo)) throw new ParamInvalidException('订单号不能为空');
            $userId = request('userId');
            if(empty($userId)) throw new ParamInvalidException('用户ID不能为空');

            $page = request('page', 'pages/severs/index/index');

            $order = OrderService::getInfoByOrderNo($orderNo);
            if(empty($order)){
                throw new DataNotFoundException('订单信息不存在');
            }
            if($order->user_id != $userId){
                throw new NoPermissionException('订单信息不存在');
            }

            if($order->pay_target_type == Order::PAY_TARGET_TYPE_PLATFORM){
                $targetOperId = 0;
            }

            $scene = new MiniprogramScene();
            $scene->oper_id = $targetOperId;
            $scene->page = $page;
            $scene->type = MiniprogramScene::TYPE_PAY_BRIDGE;
            $scene->payload = json_encode([
                'order_no' => $orderNo,
                'user_id' => $userId
            ]);
            $scene->save();

            $appCodeUrl = WechatService::getMiniprogramAppCodeUrl($scene);
        }catch (\App\Exceptions\MiniprogramPageNotExistException $e){
            $appCodeUrl = '';
            $errorMsg = '小程序页面不存在或尚未发布';
        }catch (BaseResponseException $e){
            $appCodeUrl = '';
            $errorMsg = $e->getResponse()->original['message'];
        }catch (Exception $e){
            $appCodeUrl = '';
            $errorMsg = $e->getMessage();
        }

//    $appCodeUrl = 'https://o2o.niucha.ren/storage/miniprogram/app_code/_3-id=52.jpg';
        return view('miniprogram_bridge.pay', [
            'app_code_url' => $appCodeUrl,
            'errorMsg' => $errorMsg ?? null,
        ]);
    }

    /**
     * 支付通知接口, 用于接收微信支付的通知
     * @throws \EasyWeChat\Kernel\Exceptions\Exception
     */
    public function notify()
    {
        $str = request()->getContent();
        $xml = simplexml_load_string($str);
        // 获取appid
        foreach ($xml->children() as $child) {
            if(strtolower($child->getName()) == 'appid'){
                $appid = $child . '';
            }
        }
        // 获取appid对应的运营中心小程序
        $miniprogram = OperMiniprogramService::getByAppid($appid);

        $app = WechatService::getWechatPayAppForOper($miniprogram);
        $response = $app->handlePaidNotify(function ($message, $fail){
            if($message['return_code'] === 'SUCCESS' && array_get($message, 'result_code') === 'SUCCESS'){
                $orderNo = $message['out_trade_no'];
                $totalFee = $message['total_fee'];
                OrderService::paySuccess($orderNo, $message['transaction_id'], $totalFee / 100);
            } else {
                return $fail('通信失败，请稍后再通知我');
            }

            // 其他未知情况
            return false;
        });
        return $response;
    }

    /**
     * 本地模拟支付成功
     * @throws Exception
     */
    public function mockPaySuccess()
    {
        if(App::environment() === 'local' || App::environment() === 'test'){
            OrderService::paySuccess(request('order_no'), 'mock transaction id', 0, 0);
            return Result::success('模拟支付成功');
        }else {
            abort(404);
        }
    }


    /**
     * 融宝退款通知
     */
    public function refundRealpay()
    {
        $reapal = request()->getContent();
        LogDbService::reapalNotify(LogOrderNotifyReapal::TYPE_REFUND, $reapal);
    }

    /**
     * 融宝支付通知接口, 用于接收微信支付的通知
     */
    public function notifyRealpay()
    {
        /*$reapal = new ReapalPay();

        $request =  $reapal->payNotify();
        var_dump($request);die();*/
        //获取参数
        /*$resultArr = json_decode(request(),true);
        return $resultArr;*/

        $reapal = request()->getContent();
        //$reapal = 'data=%7B%22notify_id%22%3A%223bf4cce100a94544ab65bcbd80fa5613%22%2C%22open_id%22%3A%22oA7-Z5blKW1JGt2Cf7c8LRvmpe9s%22%2C%22order_no%22%3A%22O20180830203036729649%22%2C%22order_time%22%3A%222018-08-30+20%3A30%3A37%22%2C%22sign%22%3A%22ff61f3abc45c9b3a7533a20b59292d79%22%2C%22status%22%3A%22TRADE_FINISHED%22%2C%22store_name%22%3A%22%E7%A8%8B%E7%A8%8B%E5%AE%B6%22%2C%22store_phone%22%3A%2215989438364%22%2C%22total_fee%22%3A%221%22%2C%22trade_no%22%3A%2210180830003914450%22%7D&merchant_id=100000001304038&encryptkey=';
        LogDbService::reapalNotify(LogOrderNotifyReapal::TYPE_PAY,$reapal);
        parse_str($reapal,$url_params_arr);
        $data = json_decode($url_params_arr['data'],true);

        if (!empty($data['trade_no']) && $data['status'] == 'TRADE_FINISHED') {
            OrderService::paySuccess($data['order_no'], $data['trade_no'], $data['total_fee'] / 100,Order::PAY_TYPE_REAPAL);
            echo 'success';
        } else {
            echo 'fail';
        }

        exit;

    }
}