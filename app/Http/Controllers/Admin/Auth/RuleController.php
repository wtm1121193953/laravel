<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/4
 * Time: 18:31
 */

namespace App\Http\Controllers\Admin\Auth;


use App\Http\Controllers\Controller;
use App\Modules\Admin\AdminRuleService;
use App\Result;

class RuleController extends Controller
{

    public function getList()
    {
        $list = AdminRuleService::getTreeList();

        return Result::success([
            'list' => $list,
        ]);
    }

    public function getTree()
    {
        $rules = AdminRuleService::getAll();
        // 封装权限列表, 子权限要在父权限的后面
        $tree = AdminRuleService::convertRulesToTree($rules);
        //$tree = AdminRuleService::convertRulesToTreeByRecursion($rules);

        return Result::success([
            'tree' => $tree,
            'list' => $rules,
        ]);
    }

    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
        ]);

        $rule = AdminRuleService::addFromRequest();

        return Result::success($rule);
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required'
        ]);
        $rule = AdminRuleService::editFromRequest(request('id'));

        return Result::success($rule);
    }

    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer'
        ]);
        $rule = AdminRuleService::changeStatus(request('id'), request('status'));

        return Result::success($rule);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function del()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1'
        ]);

        $rule = AdminRuleService::del(request('id'));

        return Result::success($rule);
    }
}