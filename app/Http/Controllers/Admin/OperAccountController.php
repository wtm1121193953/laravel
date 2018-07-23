<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Oper\OperAccountService;
use App\Result;

class OperAccountController extends Controller
{

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

        $account = OperAccountService::createAccount(request('oper_id'), request('account'), request('password'));

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
        $account = OperAccountService::editAccount(request('id'), request('oper_id'), request('password'));

        return Result::success($account);
    }

}