<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/23
 * Time: 16:35
 */

namespace App\Modules\Oper;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\ResultCode;

class OperAccountService extends BaseService
{

    /**
     * 根据运营中心 ID获取运营中心账号
     * @param $operId
     * @return OperAccount
     */
    public static function getByOperId($operId)
    {
        return OperAccount::where('oper_id', $operId)->first();
    }

    /**
     * 根据账号ID获取运营中心账号
     * @param $id
     * @return OperAccount
     */
    public static function getById($id)
    {
        return OperAccount::find($id);
    }

    /**
     * 添加运营中心账号
     * @param $operId int
     * @param $account string
     * @param $password string
     * @return OperAccount
     */
    public static function createAccount($operId, $account, $password)
    {

        $obj = self::getByOperId($operId);
        if(!empty($obj)){
            throw new BaseResponseException('该运营中心账户已存在, 不能重复创建', ResultCode::ACCOUNT_EXISTS);
        }
        // 查询账号是否重复
        if(!empty(OperAccount::where('account', request('account'))->first())){
            throw new BaseResponseException('帐号重复, 请更换帐号');
        }
        $obj = new OperAccount();

        $obj->account = $account;
        $obj->oper_id = $operId;
        $salt = str_random();
        $obj->salt = $salt;
        $obj->password = OperAccount::genPassword($password, $salt);
        $obj->save();

        return $obj;
    }

    /**
     * 编辑运营中心账号信息, 即修改密码
     * @param $id
     * @param $operId
     * @param $password
     */
    public static function editAccount($id, $operId, $password)
    {
        $account = self::getById($id);
        if(empty($account)){
            throw new BaseResponseException('该运营中心账号不存在, 请先创建账号', ResultCode::API_NOT_FOUND);
        }
        $account->oper_id = $operId;
        $salt = str_random();
        $account->salt = $salt;
        $account->password = OperAccount::genPassword($password, $salt);

        $account->save();
        return $account;
    }
}