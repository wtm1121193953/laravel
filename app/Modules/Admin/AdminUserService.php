<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/15
 * Time: 15:24
 */

namespace App\Modules\Admin;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\ResultCode;

class AdminUserService extends BaseService
{

    /**
     * 获取全部用户
     */
    public static function getAllUsers()
    {
        return AdminUser::all();
    }

    /**
     * 根据ID获取用户信息
     * @param $id
     * @return AdminUser
     */
    public static function getById($id)
    {
        return AdminUser::find($id);
    }

    /**
     * 根据用户名获取用户
     * @param $username
     * @return AdminUser
     */
    public static function getByUsername($username)
    {
        return AdminUser::where('username', $username)->first();
    }

    /**
     * 添加用户
     * @param $username
     * @param $password
     * @param int $groupId
     * @param int $status
     * @param bool $isSuper
     * @return AdminUser
     */
    public static function add($username, $password, $groupId = 0, $status = 1, $isSuper = false)
    {
        $user = self::getByUsername($username);
        if($user){
            throw new BaseResponseException('帐号已存在', ResultCode::ACCOUNT_EXISTS);
        }
        $user = new AdminUser();
        $user->username = $username;
        $salt = str_random();
        $user->salt = $salt;
        $user->password = AdminUser::genPassword($password, $salt);
        $user->group_id = $groupId;
        $user->super = $isSuper ? 1 : 2;
        $user->status = $status;
        $user->save();

        return $user;
    }

    /**
     * 编辑用户信息
     * @param $id
     * @param $username
     * @param int $groupId
     * @param int $status
     * @return AdminUser
     */
    public static function edit($id, $username, $groupId = 0, $status = 1)
    {
        $user = self::getById($id);
        if(empty($user)){
            throw new DataNotFoundException('用户信息不存在');
        }
        $user = AdminUser::where('username', $username)
            ->where('id', '<>', $id)
            ->first();
        if($user){
            throw new BaseResponseException('帐号名重复', ResultCode::ACCOUNT_EXISTS);
        }
        $user->username = $username;
        $user->group_id = $groupId;
        $user->status = $status;
        $user->save();

        return $user;
    }


}