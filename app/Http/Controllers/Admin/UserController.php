<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/1
 * Time: 17:31
 */

namespace App\Http\Controllers\Admin;

use App\Exceptions\BaseResponseException;
use App\Exceptions\NoPermissionException;
use App\Http\Controllers\Controller;
use App\Modules\Admin\AdminUser;
use App\Result;
use App\ResultCode;

class UserController extends Controller
{

    public function getList()
    {
        $list = AdminUser::all();
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
        $user = AdminUser::where('username', request('username'))->first();
        if($user){
            throw new BaseResponseException('账号已存在', ResultCode::ACCOUNT_EXISTS);
        }
        $user = new AdminUser();
        $user->username = request('username');
        $salt = str_random();
        $user->salt = $salt;
        $user->password = AdminUser::genPassword(request('password'), $salt);
        $user->group_id = request('group_id', 0);
        $user->super = 2;
        $user->status = request('status', 1);
        $user->save();
        return Result::success($user);
    }

    public function edit()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'username' => 'required',
        ]);
        $user = AdminUser::findOrFail(request('id'));
        $user->username = request('username');
        $user->group_id = request('group_id', 0);
        $user->status = request('status', 1);
        $user->save();
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
        $user = AdminUser::findOrFail(request('id'));
        $salt = str_random();
        $user->salt = $salt;
        $user->password = AdminUser::genPassword(request('password'), $salt);
        $user->save();
        return Result::success($user);
    }

    public function changeStatus()
    {
        $this->validate(request(), [
            'id' => 'required|integer|min:1',
            'status' => 'required|integer'
        ]);
        $user = AdminUser::findOrFail(request('id'));
        $user->status = request('status');
        $user->save();
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
        $user = AdminUser::findOrFail(request('id'));
        if($user->isSuper()){
            throw new BaseResponseException('无权限删除', ResultCode::NO_PERMISSION);
        }
        $user->delete();
        return Result::success($user);
    }
}