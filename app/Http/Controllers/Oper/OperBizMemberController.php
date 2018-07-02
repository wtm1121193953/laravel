<?php

namespace App\Http\Controllers\Oper;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\OperBizMember;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

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
        $data = OperBizMember::where('oper_id', request()->get('current_user')->oper_id)
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

        $data->each(function($item) {

            $item->activeMerchantNumber = OperBizMember::getActiveMerchantNumber($item);

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
        $list = OperBizMember::where('oper_id', request()->get('current_user')->oper_id)
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
        $operBizMember = new OperBizMember();
        $operBizMember->oper_id = request()->get('current_user')->oper_id;
        $operBizMember->name = request('name');
        $operBizMember->mobile = request('mobile');
        $operBizMember->remark = request('remark', '');
        $operBizMember->status = request('status', 1);

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
        $operBizMember = OperBizMember::findOrFail(request('id'));
        $operBizMember->name = request('name');
//        $operBizMember->mobile = request('mobile');
        $operBizMember->remark = request('remark', '');

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
        $operBizMember = OperBizMember::findOrFail(request('id'));
        $operBizMember->status = request('status');

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
        $code = request('code');
        $data = Merchant::where(function (Builder $query){
            $query->where('oper_id', request()->get('current_user')->oper_id)
                ->orWhere('audit_oper_id',  request()->get('current_user')->oper_id);
            })
            ->where('oper_biz_member_code', $code)
            ->select('id', 'active_time', 'name', 'status')
            ->paginate();

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

}