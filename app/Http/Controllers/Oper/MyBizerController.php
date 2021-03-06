<?php

namespace App\Http\Controllers\Oper;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Bizer\BizerService;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\OperBizerService;
use App\Modules\Oper\OperBizer;
use App\Modules\Oper\MyOperBizer;

use App\Result;
use Illuminate\Database\Eloquent\Builder;

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
    public function edit()
    {
        $id = request('id');
        $status = request('status');
        $divide = request('divide');
        $remark = request('remark');

        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'integer|range:0,2',
            'divide' => 'min:0|max:100'
        ]);
        $operBizer = OperBizer::findOrFail($id);
        if(!empty($status)){
            $operBizer->status = $status;
        }
        if(!is_null($divide) && $divide >= 0 && $divide <= 100){
            $operBizer->divide = $divide;
        }
        if(!empty($remark)){
            $operBizer->remark = $remark;
        }
        $operBizer->save();
        return Result::success($operBizer);
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

    /**
     * 改变业务员签约状态
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeOperBizerSignStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $operBizer = OperBizerService::getById($id);
        if (empty($operBizer)) {
            throw new BaseResponseException('该业务员不存在');
        }
        $operBizer->sign_status = $operBizer->sign_status == OperBizer::SIGN_STATUS_ON ? OperBizer::SIGN_STATUS_OFF : OperBizer::SIGN_STATUS_ON;
        $operBizer->save();

        return Result::success($operBizer);
    }

    /**
     * 获取业务员列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBizerList()
    {
        $operId = request()->get('current_user')->oper_id;
        $bizerNameOrMobile = request('bizerNameOrMobile', '');
        $bizerList = BizerService::getBizersByNameOrMobile($bizerNameOrMobile, $operId);

        return Result::success($bizerList);
    }

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);
        $id = request('id');
        $bizer = BizerService::getById($id);
        return Result::success($bizer);
    }

    public function getAllbizer(){
        $name = request('name', '');
        $mobile = request('mobile', '');
        $keyword = request('keyword', '');
        $status = request('status', '');
        $sign_status = request('sign_status', '');
        $where_arr = [
            "name" => $name,
            "mobile" => $mobile,
            "keyword" => $keyword,
            "status" => $status,
            'sign_status' => $sign_status,
            'oper_ids' => request()->get('current_user')->oper_id,
        ];
        $data = OperBizerService::getAllbizer($where_arr);
        return Result::success([
            'list' => $data,
        ]);
    }

}
