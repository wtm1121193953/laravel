<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/5
 * Time: 21:29
 */

namespace App\Http\Controllers\Admin\Auth;


use App\Http\Controllers\Controller;
use App\Modules\Admin\AdminGroupService;
use App\Result;

class GroupController extends Controller
{

    public function getList()
    {
        return Result::success([
            'list' => AdminGroupService::getAllGroups(),
        ]);
    }

    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
        ]);
        $group = AdminGroupService::add(
            request('name'),
            request('rule_ids', ''),
            request('status', 1)
        );

        return Result::success($group);
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
        ]);
        $group = AdminGroupService::edit(
            request('id'),
            request('name'),
            request('rule_ids', ''),
            request('status', 1)
        );

        return Result::success($group);
    }

    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $group = AdminGroupService::changeStatus(request('id'), request('status', 1));

        return Result::success($group);
    }

    /**
     * @throws \Exception
     */
    public function del()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $group = AdminGroupService::del(request('id'));
        return Result::success($group);
    }
}