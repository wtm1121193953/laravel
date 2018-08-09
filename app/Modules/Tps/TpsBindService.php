<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/23
 * Time: 16:35
 */

namespace App\Modules\Tps;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\ResultCode;

class TpsBindService extends BaseService
{

    /**
     * 根据账号查询TPS绑定信息
     * @param $tps_bind_account
     * @return TpsBind
     */
	public static function getBindByaccount($tps_bind_account)
    {
    	return TpsBind::where('tps_bind_account', $tps_bind_account)->first();
    }

    /**
     * 添加账号
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

}