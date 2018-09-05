<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/15
 * Time: 15:24
 */

namespace App\Modules\Admin;


use App\BaseService;
use App\Exceptions\AccountNotFoundException;
use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\NoPermissionException;
use App\Exceptions\PasswordErrorException;
use App\ResultCode;
use Illuminate\Database\Eloquent\Builder;

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
        if ($user) {
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
        if (empty($user)) {
            throw new DataNotFoundException('用户信息不存在');
        }
        $exist = AdminUser::where('username', $username)
            ->where('id', '<>', $id)
            ->first();
        if ($exist) {
            throw new BaseResponseException('帐号名重复', ResultCode::ACCOUNT_EXISTS);
        }
        $user->username = $username;
        $user->group_id = $groupId;
        $user->status = $status;
        $user->save();

        return $user;
    }

    /**
     * 重置密码
     * @param int|AdminUser $id
     * @param $password
     * @return AdminUser
     */
    public static function resetPassword($id, $password)
    {
        if($id instanceof AdminUser){
            $user = $id;
        }else {
            $user = self::getById($id);
        }
        if (empty($user)) {
            throw new DataNotFoundException('用户信息不存在');
        }
        $salt = str_random();
        $user->salt = $salt;
        $user->password = AdminUser::genPassword($password, $salt);
        $user->save();
        return $user;
    }

    /**
     * @param $username
     * @param $password
     * @return AdminUser
     */
    public static function checkPasswordByUsername($username, $password)
    {
        $user = self::getByUsername($username);
        if (empty($user)) {
            throw new AccountNotFoundException();
        }
        if(!self::checkPassword($user, $password)){
            throw new PasswordErrorException();
        }
        return $user;
    }

    /**
     * 检查密码是否正确
     * @param AdminUser $user
     * @param $password
     * @return bool
     */
    public static function checkPassword(AdminUser $user, $password)
    {
        return AdminUser::genPassword($password, $user->salt) == $user->password;
    }

    /**
     * 修改密码
     * @param int|AdminUser $id
     * @param $password
     * @param $newPassword
     * @return AdminUser|int
     */
    public static function modifyPassword($id, $password, $newPassword)
    {
        if($id instanceof AdminUser){
            $user = $id;
        }else {
            $user = self::getById($id);
            if(empty($user)){
                throw new AccountNotFoundException();
            }
        }

        // 检查原密码是否正确
        if(!self::checkPassword($user, $password)){
            throw new PasswordErrorException();
        }

        $user = self::resetPassword($user, $newPassword);
        return $user;
    }

    /**
     * 改变用户状态
     * @param $id
     * @param $status
     * @return AdminUser
     */
    public static function changeStatus($id, $status)
    {
        $user = self::getById($id);
        if (empty($user)) {
            throw new DataNotFoundException('用户信息不存在');
        }
        $user->status = $status;
        $user->save();
        return $user;
    }

    /**
     * 删除用户
     * @param $id
     * @return AdminUser
     * @throws \Exception
     */
    public static function del($id)
    {
        $user = self::getById($id);
        if (empty($user)) {
            throw new DataNotFoundException('用户信息不存在');
        }
        if ($user->isSuper()) {
            throw new NoPermissionException('无权限删除');
        }
        $user->delete();
        return $user;
    }

    /**
     * 获取用户的权限列表
     * @param AdminUser $user
     * @param bool $enable
     * @return AdminAuthRule[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getUserRules(AdminUser $user, $enable = true)
    {
        if ($user->isSuper()) {
            $rules = AdminAuthRule::when($enable, function (Builder $query) {
                $query->where('status', AdminAuthRule::STATUS_ON);
            })->orderBy('sort')->get();
        } else {
            $ruleIds = AdminAuthGroup::where('id', $user->group_id)->value('rule_ids');
            $rules = AdminAuthRule::when($enable, function (Builder $query) {
                $query->where('status', AdminAuthRule::STATUS_ON);
            })->whereIn('id', explode(',', $ruleIds))->orderBy('sort')->get();
        }
        return $rules;
    }

    /**
     * 获取用户菜单(权限树)
     * @param AdminUser $user
     * @param bool $enable
     * @return array
     */
    public static function getUserRulesTree(AdminUser $user, $enable = true)
    {
        $rules = self::getUserRules($user, $enable);
        $tree = AdminRuleService::convertRulesToTree($rules);
        return $tree;
    }

}