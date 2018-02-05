<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/5
 * Time: 21:29
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Modules\Admin\AdminAuthGroup;
use App\Result;

class GroupController extends Controller
{

    public function getList()
    {
        return Result::success([
            'list' => AdminAuthGroup::all(),
        ]);
    }

    public function add()
    {
        $this->validate(request(), [
            'name' => 'required',
        ]);
        $group = new AdminAuthGroup();
        $group->name = request('name');
        $group->status = request('status', 1);
        $group->rule_ids = request('rule_ids', '');
        $group->save();

        return Result::success($group);
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'name' => 'required',
        ]);
        $group = AdminAuthGroup::findOrFail(request('id'));
        $group->name = request('name');
        $group->status = request('status', 1);
        $group->rule_ids = request('rule_ids', '');
        $group->save();

        return Result::success($group);
    }

    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);
        $group = AdminAuthGroup::findOrFail(request('id'));
        $group->status = request('status', 1);
        $group->save();

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
        $group = AdminAuthGroup::findOrFail(request('id'));
        $group->delete();
        return Result::success($group);
    }
}