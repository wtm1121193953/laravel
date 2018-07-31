<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/4
 * Time: 18:31
 */

namespace App\Http\Controllers\Admin\Auth;


use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Admin\AdminAuthRule;
use App\Modules\Admin\AdminService;
use App\Result;
use App\ResultCode;

class RuleController extends Controller
{

    private function getRuleListFromTree($tree, &$list)
    {
        foreach ($tree as &$item) {
            $list[] = &$item;
            if(isset($item['sub']) && is_array($item['sub']) && sizeof($item['sub']) > 0){
                $this->getRuleListFromTree($item['sub'], $list);
            }
        }
        return $list;
    }

    public function getList()
    {
        $rules = AdminAuthRule::orderBy('sort')->get();
        // 封装权限列表, 子权限要在父权限的后面
        $tree = AdminService::convertRulesToTree($rules);
        $list = [];
        $list = $this->getRuleListFromTree($tree, $list);

        return Result::success([
            'list' => $list,
        ]);
    }

    public function getTree()
    {
        $rules = AdminAuthRule::orderBy('sort')->get();
        // 封装权限列表, 子权限要在父权限的后面
        $tree = AdminService::convertRulesToTree($rules);

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

        $rule = new AdminAuthRule();

        $rule->name = request('name', '');
        $rule->pid = request('pid', 0);
        $rule->level = $rule->pid == 0 ? 1 : 2;
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
        $rule->pid = request('pid', 0);
        $rule->level = $rule->pid == 0 ? 1 : 2;
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

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
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