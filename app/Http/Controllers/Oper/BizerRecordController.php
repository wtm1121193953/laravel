<?php

namespace App\Http\Controllers\Oper;

use App\Http\Controllers\Controller;
use App\Modules\Oper\OperService;
use App\Modules\Oper\OperBizerService;
use App\Modules\Oper\OperBizMember;

use App\Result;

class BizerRecordController extends Controller {

    /**
     * 业务员申请
     * @return type
     */
    public function getList() {
        $where =[
            "oper_ids" => request()->get('current_user')->oper_id,//登录所属运营中心ID
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
        ]);

        $id = request('id');
        $status = request('status');
        $remark = request('remark');
        $operBizMember = OperBizMember::findOrFail($id);
        $operBizMember->status = $status;
        $operBizMember->remark = $remark;

        $operBizMember->save();
        return Result::success($operBizMember);
    }
}
