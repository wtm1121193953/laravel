<?php

namespace App\Http\Controllers\Oper;

use App\Http\Controllers\Controller;
use App\Modules\Oper\OperBizerService;
use App\Modules\Oper\OperBizer;

use App\Result;

class BizerRecordController extends Controller {

    /**
     * 业务员申请，默认申请中的业务员
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList() {
        $status = request("selectStatus")=='first' ? 0 : -1;
        $where =[
            "oper_ids" => request()->get('current_user')->oper_id,//登录所属运营中心ID
            "status" =>$status,//查询业务员的状态
        ];

        $data = OperBizerService::getList($where);
        
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 签约或者拒绝签约，修改状态
     * @return \Illuminate\Http\JsonResponse
     */
    public function contractBizer()
    {
        $validate = array(
            'id' => 'required|integer|min:1',
            'status' => 'required|integer',
            'divide' => 'required|integer',
        );
        if(request('status')=='-1'){
            unset($validate["divide"]);
        }
        $this->validate(request(),$validate);

        $id = request('id');
        $status = request('status');
        $note = request('note', '');
        $divide = request('divide', 0);
        $operBizMember = OperBizer::findOrFail($id);
        $operBizMember->status = $status;
        $operBizMember->note = $note;
        if($status==1){//签约成功，更新签约时间,分成比例
            $operBizMember->divide = number_format($divide,2);
            $operBizMember->sign_time = date("Y-m-d H:i:s");
        }

        $operBizMember->save();
        return Result::success($operBizMember);
    }
}
