<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/1
 * Time: 17:31
 */

namespace App\Http\Controllers\Admin\Auth;

use App\Exceptions\NoPermissionException;
use App\Http\Controllers\Controller;
use App\Modules\Admin\AdminUserService;
use App\Result;

class UserController extends Controller
{

    public function getList()
    {
        $list = AdminUserService::getAllUsers();
        return Result::success([
            'list' => $list
        ]);
    }

    public function add()
    {
        $this->validate(request(), [
            'username' => 'required',
            'password' => 'required|between:6,30'
        ]);

        $user = AdminUserService::add(
            request('username'),
            request('password'),
            request('group_id', 0),
            request('status', 1)
        );

        return Result::success($user);
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'username' => 'required',
        ]);
        $user = AdminUserService::edit(
            request('id'),
            request('username'),
            request('group_id', 0),
            request('status', 1)
        );
        return Result::success($user);
    }

    public function resetPassword()
    {
        $currentUser = request()->get('current_user');
        if(!$currentUser->isSuper()){
            throw new NoPermissionException();
        }
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'password' => 'required|between:6,30'
        ]);
        $user = AdminUserService::resetPassword(request('id'), request('password'));
        return Result::success($user);
    }

    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer'
        ]);
        $user = AdminUserService::changeStatus(request('id'), request('status'));
        return Result::success($user);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function del()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
        ]);

        $user = AdminUserService::del(request('id'));

        return Result::success($user);
    }
}