<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/4
 * Time: 18:31
 */

namespace App\Http\Controllers\Admin;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Admin\AdminAuthRule;
use App\Result;
use App\ResultCode;

class RuleController extends Controller
{

    public function getList()
    {
        // todo 封装权限列表, 子权限要在父权限的后面
        return Result::success([
            'list' => AdminAuthRule::all()
        ]);
    }

    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
        ]);

        $rule = new AdminAuthRule();

        $rule->name = request('name', '');
        $rule->url = request('url', '');
        $rule->url_all = request('url_all', '');
        $rule->status = request('status', 1);
        $rule->sort = request('sort', 1);
        $rule->save();

        return Result::success($rule);
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required'
        ]);
        $rule = AdminAuthRule::findOrFail(request('id'));

        $rule->name = request('name', '');
        $rule->url = request('url', '');
        $rule->url_all = request('url_all', '');
        $rule->status = request('status', 1);
        $rule->sort = request('sort', 1);
        $rule->save();

        return Result::success($rule);
    }

    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer'
        ]);
        $rule = AdminAuthRule::findOrFail(request('id'));

        $rule->status = request('status');
        $rule->save();

        return Result::success($rule);
    }

    public function del()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);

        $rule = AdminAuthRule::findOrFail(request('id'));

        if(empty($rule->created_at)){
            throw new BaseResponseException('该权限不能删除', ResultCode::NO_PERMISSION);
        }

        $rule->delete();

        return Result::success($rule);
    }
}