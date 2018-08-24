<?php

namespace App\Http\Controllers\Oper;

use App\Http\Controllers\Controller;
use App\Modules\Oper\OperService;
use App\Modules\Oper\OperBizerService;
use App\Modules\Oper\OperBizMember;

use App\Result;

class BizerRecordController extends Controller {

    /**
     * 业务员申请，默认申请中的业务员
     * @return type
     */
    public function getList() {
        $where =[
            "oper_ids" => request()->get('current_user')->oper_id,//登录所属运营中心ID
            "status" =>0,//默认获取申请中的业务员
        ];
        //echo "<pre>";print_r($where);exit;
        $data = OperBizerService::getList($where);
        
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
    /**
     * 获取已经拒绝的业务员申请
     * @return type
     */
    public function getRefuseList() {
        $where =[
            "oper_ids" => request()->get('current_user')->oper_id,//登录所属运营中心ID
            "status" =>-1,//默认获取申请中的业务员
        ];
        //echo "<pre>";print_r($where);exit;
        $data = OperBizerService::getList($where);
        
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
    /**
     * 签约或者拒绝签约，修改状态
     * @return type
     */
    public function contractBizer()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer',
            'remark' => 'required|integer',
            'divide' => 'required',
        ]);

        $id = request('id');
        $status = request('status');
        $remark = request('remark');
        $divide = request('divide');
        $operBizMember = OperBizMember::findOrFail($id);
        $operBizMember->status = $status;
        $operBizMember->remark = $remark;
        if($status==1){//签约成功，更新签约时间,分成比例
            $operBizMember->divide = number_format(2,$divide/100);        
            $operBizMember->sign_time = date("Y-m-d H:i:s");
        }
        $operBizMember->save();
        return Result::success($operBizMember);
    }
}
