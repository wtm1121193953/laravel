<?php

namespace App\Http\Controllers\Oper;


use App\Http\Controllers\Controller;
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
        $data = OperBizMember::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'desc')->paginate();

        $data->each(function($item) {

            $item->activeMerchantNumber = OperBizMember::getActiveMerchantNumber($item);

        });

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
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

}