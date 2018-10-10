<?php

namespace App\Http\Controllers\Oper;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\OperBizMember;
use App\Modules\Bizer\BizerService;
use App\Result;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Oper\OperBizerService;

/**
 * 原有的业务员操作不再提取到service中, 后面会去掉
 * Class OperBizMemberController
 * @package App\Http\Controllers\Oper
 * @deprecated
 */
class OperBizMemberController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $status = request('status');
        $name = request('name');
        $mobile = request('mobile');
        $operId = request()->get('current_user')->oper_id;

        $data = OperBizMember::where('oper_id', $operId)
            ->when($status, function (Builder $query) use ($status){
                $query->where('status', $status);
            })
            ->when($name, function (Builder $query) use ($name) {
                $query->where('name', 'like', "%$name%");
            })
            ->when($mobile, function (Builder $query) use ($mobile){
                $query->where('mobile', 'like', "%$mobile%");
            })
            ->orderBy('id', 'desc')->paginate();

        $data->each(function($item) use ($operId){

            $item->activeMerchantNumber = OperBizMember::getActiveMerchantNumber($item,request()->get('current_user')->oper_id);
            $item->auditMerchantNumber = OperBizMember::getAuditMerchantNumber($item,request()->get('current_user')->oper_id);

        });

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 搜索业务员
     */
    public function search()
    {
        $code = request('code', '');
        $name = request('name', '');
        $mobile = request('mobile', '');
        $keyword = request('keyword', '');
        $status = request('status');
        $operId = request()->get('current_user')->oper_id;
        $list = OperBizMember::where('oper_id', $operId)
            ->when($status, function(Builder $query) use ($status){
                $query->where('status', $status);
            })
            ->when(!empty($keyword), function(Builder $query) use ($keyword){
                $query->where(function(Builder $query) use ($keyword){
                    $query->where('code', 'like', "%$keyword%")
                        ->orWhere('name', 'like', "%$keyword%")
                        ->orWhere('mobile', 'like', "%$keyword%");
                });
            })
            ->when(!empty($code), function(Builder $query) use ($code) {
                $query->where('code', 'like', "%$code%");
            })
            ->when(!empty($name), function(Builder $query) use ($name){
                $query->where('name', 'like', "%$name%");
            })
            ->when(!empty($mobile), function(Builder $query) use ($mobile){
                $query->where('mobile', 'like', "%$mobile%");
            })->get();
        return Result::success([
            'list' => $list
        ]);
    }

    public function detail()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $id = request('id');
        $operBizMember = OperBizMember::findOrFail($id);
        return Result::success($operBizMember);
    }

    /**
     * 添加数据
     */
    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
            'mobile' => 'required',
        ]);

        $name = request('name');
        $mobile = request('mobile');
        $remark = request('remark','');
        $status = request('status', 1);
        $operId = request()->get('current_user')->oper_id;

        $haveMemberMobile = OperBizMember::where( 'mobile' , $mobile)
            ->where('oper_id', $operId)
            ->get();
        if (count($haveMemberMobile) > 0){
            throw new BaseResponseException('手机号码重复');
        }

        $operBizMember = new OperBizMember();
        $operBizMember->oper_id = $operId;
        $operBizMember->name = $name;
        $operBizMember->mobile = $mobile;
        $operBizMember->remark = $remark;
        $operBizMember->status = $status;

        $operBizMember->code = OperBizMember::genCode();

        $operBizMember->save();

        return Result::success($operBizMember);
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
        ]);

        $id = request('id');
        $name = request('name');
        $mobile = request('mobile');
        $remark = request('remark', '');
        $operId = request()->get('current_user')->oper_id;

        $haveMemberMobile = OperBizMember::where( 'mobile' , $mobile)
            ->where('oper_id', $operId)
            ->where('id', '<>', $id)
            ->get();
        if (count($haveMemberMobile) > 0){
            throw new BaseResponseException('手机号码重复');
        }

        $operBizMember = OperBizMember::findOrFail($id);
        $operBizMember->name = $name;
        $operBizMember->mobile = $mobile;
        $operBizMember->remark = $remark;

        $operBizMember->save();

        return Result::success($operBizMember);
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

        $id = request('id');
        $status = request('status');
        $operBizMember = OperBizMember::findOrFail($id);
        $operBizMember->status = $status;

        $operBizMember->save();
        return Result::success($operBizMember);
    }

    /**
     * 获取业务员的商户
     */
    public function getMerchants()
    {
        $this->validate(request(), [
            'code' => 'required',
        ]);
        $operId = request()->get('current_user')->oper_id;
        $code = request('code');
        $data = Merchant::where(function (Builder $query) use ($operId){
            $query->where('oper_id', $operId)
                ->orWhere('audit_oper_id',  $operId);
            })
            ->where('oper_biz_member_code', $code)
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
     * 获取员工列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberList()
    {
        $operId = request()->get('current_user')->oper_id;
        $memberNameOrMobile = request('memberNameOrMobile', '');
        $memberList = OperBizerService::getOperBizMembersByNameOrMobile($memberNameOrMobile, $operId);

        return Result::success($memberList);
    }
}