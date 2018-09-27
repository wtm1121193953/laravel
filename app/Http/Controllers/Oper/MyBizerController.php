<?php

namespace App\Http\Controllers\Oper;

use App\Http\Controllers\Controller;
use App\Modules\Oper\OperService;
use App\Modules\Oper\OperBizerService;
use App\Modules\Oper\OperBizer;
use App\Modules\Oper\MyOperBizer;

use App\Result;

class MyBizerController extends Controller {

    /**
     * 业务员申请，默认申请中的业务员
     * @return type
     */
    public function getList() {
        $where =[
            "oper_id" => request()->get('current_user')->oper_id,//登录所属运营中心ID
        ];

        $data = MyOperBizer::getList($where);
        
        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
    /**
     * 修改状态,分成，备注
     */
    public function changeDetail()
    {
        $id = request('id');
        $sign_status = request('sign_status');
        $status = request('status');
        $divide = request('divide');
        $remark = request('remark');
        $validate = array('id' => 'required|integer|min:1');
        if(!empty($status)){
            $validate["status"] = 'required|integer';
        }
        if(!empty($divide)){
            $validate["divide"] = 'required';
        }
        $this->validate(request(),$validate);
        $operBizMember = OperBizer::findOrFail($id);
        if (!empty($sign_status)) {
            $operBizMember->sign_status = $sign_status;
        }
        if(!empty($status)){
            $operBizMember->status = $status;
        }
        if(!empty($divide)){
            $operBizMember->divide = $divide;
        }
        if(!empty($remark)){
            $operBizMember->remark = $remark;
        }
        $operBizMember->save();
        return Result::success($operBizMember);
    }
    /**
     * 获取业务员的商户
     */
    public function getMerchants()
    {
        $this->validate(request(), [
            'bizer_id' => 'required|integer',
        ]);
        $operId = request()->get('current_user')->oper_id;
        $bizer_id = request('bizer_id');
        $data = Merchant::where(function (Builder $query) use ($operId){
            $query->where('oper_id', $operId)
                ->orWhere('audit_oper_id',  $operId);
            })
            ->where('bizer_id', $bizer_id)
            ->select('id', 'active_time', 'name', 'status','audit_status','created_at','is_pilot')
            ->paginate();


        $data->each(function($item) {
            $auditStatusArray = ['待审核','已审核','审核不通过','重新提交审核'];
            //      0-待审核 1-已审核 2-审核不通过 3-重新提交审核'
            $item->audit_done_time = $item->audit_status==1 ? $item->active_time : $auditStatusArray[$item->audit_status];
        });

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }
}
