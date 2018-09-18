<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Oper\OperService;
use App\Result;

class OperController extends Controller
{

    /**
     * @author  xianghua
     * 2018/6/28
     * 获取列表
     */
    public function getList()
    {
        $name = request('name');
        $status = request('status');
        $tel = request('tel');
        $payToPlatform = request('payToPlatform');

        $data = OperService::getList([
            'name' => $name,
            'status' => $status,
            'tel' => $tel,
            'payToPlatform' => $payToPlatform,
        ]);

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $oper = OperService::detail(request('id'));
        return Result::success($oper);
    }

    /**
     * 添加数据
     */
    public function add()
    {
        $max = '';
        for ($i=0;$i<30;$i++){
            $max .= '9';
        }
        $this->validate(request(), [
            'name' => 'required',
            'tel' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'bank_card_no' =>  'bail|required|min:10000000|max:'.$max.'|numeric',
        ]);

        $oper = OperService::addFromRequest();

        return Result::success($oper);
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $max = '';
        for ($i=0;$i<30;$i++){
            $max .= '9';
        }
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'bank_card_no' =>  'bail|required|min:10000000|max:'.$max.'|numeric',

        ]);
        $oper = OperService::editFromRequest(request('id'));
        return Result::success($oper);
    }

    /**
     * 修改状态
     */
    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer',
        ]);
        $oper = OperService::changeStatus(request('id'), request('status'));

        return Result::success($oper);
    }

    /**
     * 修改小程序支付对象设置
     */
    public function changePayToPlatform()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'pay_to_platform' => 'required|integer',
        ]);
        $oper = OperService::changePayToPlatform(request('id'), request('pay_to_platform'));

        return Result::success($oper);
    }

    /**
     * 支付到平台状态修改
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function setPayToPlatformStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);

        $oper = OperService::switchPayToPlatform(request('id'));

        return Result::success($oper);
    }

}