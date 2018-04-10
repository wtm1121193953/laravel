<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Oper\OperAccount;
use App\Result;
use Illuminate\Database\Eloquent\Builder;

class OperAccountController extends Controller
{

    /**
     * 获取列表 (分页)
     */
    public function getList()
    {
        $status = request('status');
        $data = OperAccount::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'desc')->paginate();

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
        ]);
    }

    /**
     * 获取全部列表
     */
    public function getAllList()
    {
        $status = request('status');
        $list = OperAccount::when($status, function (Builder $query) use ($status){
            $query->where('status', $status);
        })->orderBy('id', 'desc')->get();

        return Result::success([
            'list' => $list,
        ]);
    }

    /**
     * 添加数据
     */
    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
        ]);
        $oper_account = new OperAccount();
        $oper_account->name = request('name');
        $oper_account->status = request('status', 1);

        $oper_account->save();

        return Result::success($oper_account);
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
        $oper_account = OperAccount::findOrFail(request('id'));
        $oper_account->name = request('name');
        $oper_account->status = request('status', 1);

        $oper_account->save();

        return Result::success($oper_account);
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
        $oper_account = OperAccount::findOrFail(request('id'));
        $oper_account->status = request('status');

        $oper_account->save();
        return Result::success($oper_account);
    }

    /**
     * 删除
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function del()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $oper_account = OperAccount::findOrFail(request('id'));
        $oper_account->delete();
        return Result::success($oper_account);
    }

}