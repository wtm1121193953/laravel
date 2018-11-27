<?php
/**
 * Created by PhpStorm.
 * User: tim.tang
 * Date: 2018/10/15/015
 * Time: 12:27
 */
namespace App\Http\Controllers\Admin;

use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Payment\Payment;
use App\Modules\Payment\PaymentService;
use App\Modules\User\UserIdentityAuditRecord;
use App\Modules\User\UserIdentityAuditRecordService;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletService;
use App\Result;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Array_;

class PaymentController extends Controller
{
    public function getList()
    {
        $params = [
            'name' => request()->get('name'),
            'type' => request()->get('type'),
        ];
        $data = PaymentService::getList($params);
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required',
            'view_name' => 'required',
        ]);

        $payment = new Payment();
        $payment->name = $request->get('name');
        $payment->view_name = $request->get('view_name');
        $payment->type = $request->get('type');
        $payment->logo_url = $request->get('logo_url');
        $payment->class_name = $request->get('class_name');
        $payment->status = $request->get('status');
        $payment->on_pc = $request->get('on_pc');
        $payment->on_miniprogram = $request->get('on_miniprogram');
        $payment->configs = $request->get('configs');
        if (empty($payment->configs)) {
            $payment->configs = '';
        } else {
            $lines = explode("\n",$payment->configs);
            $configs = [];
            foreach ($lines as $l) {
                if (empty($l)) {
                    continue;
                }
                $kv = explode(':',$l);
                if (count($kv) !== 2) {
                    throw new ParamInvalidException('配置信息格式错误');
                }
                $configs[$kv[0]] = $kv[1];
            }
            $payment->configs = json_encode($configs);
        }

        if (!$payment->save()) {
            throw new BaseResponseException('执行失败');
        }
        return Result::success('添加成功');

    }

    public function detail(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
        ]);
        $data = Payment::findOrFail($request->get('id'));
        if (!empty($data->configs)) {
            $arr = json_decode($data->configs);
            $configs = [];
            foreach ($arr as $k=>$v) {
                $configs[] = "{$k}:{$v}";
            }
            $configs = implode("\n",$configs);
            $data->configs = $configs;
        }
        return Result::success($data);
    }

    public function edit(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
            'name' => 'required',
            'view_name' => 'required',
            'type' => 'required',
        ]);

        $payment = Payment::findOrFail($request->get('id'));
        $payment->name = $request->get('name');
        $payment->view_name = $request->get('view_name');
        $payment->type = $request->get('type');
        $payment->logo_url = $request->get('logo_url');
        $payment->class_name = $request->get('class_name');
        $payment->status = $request->get('status');
        $payment->on_pc = $request->get('on_pc');
        $payment->on_miniprogram = $request->get('on_miniprogram');
        $payment->configs = $request->get('configs');
        if (empty($payment->configs)) {
            $payment->configs = '';
        } else {
            $lines = explode("\n",$payment->configs);
            $configs = [];
            foreach ($lines as $l) {
                if (empty($l)) {
                    continue;
                }
                $kv = explode(':',$l);
                if (count($kv) !== 2) {
                    throw new ParamInvalidException('配置信息格式错误');
                }
                $configs[$kv[0]] = $kv[1];
            }
            $payment->configs = json_encode($configs);
        }

        if (!$payment->save()) {
            throw new BaseResponseException('执行失败');
        }
        return Result::success('更新成功');

    }

    public function getPaymentTypes()
    {
        $types = Payment::getAllType();
        return Result::success($types);
    }

    public function getListByPlatform(Request $request)
    {
        $uri = $request->getRequestUri();
        // 查询字段
        $whereArr = [
            'on_pc' =>  null,
            'on_miniprogram'    => null,
            'on_app'    =>  null,
            'type'      =>  null
        ];
        if(strpos($uri,'app')){
            //数据源于app的
            $whereArr['on_app'] = Payment::APP_ON;
        }else if(strpos($uri,'user')){
            $whereArr['on_miniprogram'] = Payment::MINI_PROGRAM_ON;
        }else{
            $whereArr['on_pc'] = Payment::PC_ON;
        }
        $wallet = WalletService::getWalletInfo($request->get('current_user'))->toArray();
        $list = PaymentService::getListByPlatForm(array_filter($whereArr),$wallet);
        $record = UserIdentityAuditRecordService::getRecordByUserId($request->get('current_user')->id);
        $wallet['identityInfoStatus'] = ($record) ? $record->status : UserIdentityAuditRecord::STATUS_UN_SAVE;
        $result = array();
        foreach ($list as $k => $v){
            array_push($result,$v);
        }
        return Result::success(['list'=>$result,'wallet'=>$wallet]);
    }

}