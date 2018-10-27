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
use App\Modules\Payment\AgentPay;
use App\Modules\Payment\AgentPayService;
use App\Result;
use Illuminate\Http\Request;

class AgentPayController extends Controller
{
    public function getList()
    {
        $params = [
            'name' => request()->get('name'),
            'type' => request()->get('type'),
        ];
        $data = AgentPayService::getList($params);
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $payment = new AgentPay();
        $payment->name = $request->get('name');
        $payment->logo_url = $request->get('logo_url');
        $payment->class_name = $request->get('class_name');
        $payment->status = $request->get('status');
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
        $data = AgentPay::findOrFail($request->get('id'));
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
        ]);

        $payment = AgentPay::findOrFail($request->get('id'));
        $payment->name = $request->get('name');
        $payment->logo_url = $request->get('logo_url') ?? '';
        $payment->class_name = $request->get('class_name');
        $payment->status = $request->get('status');
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


}