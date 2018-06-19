<?php

namespace App\Http\Controllers\Admin;


use App\Exceptions\BaseResponseException;
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
            'oper_id' => 'required|integer|min:1',
            'account' => 'required',
            'password' => 'required|min:6',
        ]);
        $account = OperAccount::where('oper_id', request('oper_id'))->first();
        if(!empty($account)){
            throw new BaseResponseException('该运营中心账户已存在, 不能重复创建');
        }
        // 查询账号是否重复
        if(!empty(OperAccount::where('account', request('account'))->first())){
            throw new BaseResponseException('账号重复, 请更换账号');
        }
        $account = new OperAccount();

        $account->account = request('account');
        $account->oper_id = request('oper_id');
        $salt = str_random();
        $account->salt = $salt;
        $account->password = OperAccount::genPassword(request('password'), $salt);
        $account->save();

        return Result::success($account);
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'oper_id' => 'required|integer|min:1',
            'password' => 'required|min:6',
        ]);
        $oper_account = OperAccount::findOrFail(request('id'));
        $oper_account->oper_id = request('oper_id');
        $salt = str_random();
        $oper_account->salt = $salt;
        $oper_account->password = OperAccount::genPassword(request('password'), $salt);

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


}