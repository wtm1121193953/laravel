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
use App\Result;
use Illuminate\Http\Request;

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
        ]);

        $payment = new Payment();
        $payment->name = $request->get('name');
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
        return Result::success($data);
    }

    public function edit(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
            'name' => 'required',
            'type' => 'required',
        ]);

        $payment = Payment::findOrFail($request->get('id'));
        $payment->name = $request->get('name');
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
            $payment = json_encode($configs);
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

}