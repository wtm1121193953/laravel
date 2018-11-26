<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/23
 * Time: 16:35
 */

namespace App\Modules\Oper;


use App\BaseService;
use App\Exceptions\AccountNotFoundException;
use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\NoPermissionException;
use App\Exceptions\PasswordErrorException;
use App\Modules\Merchant\MerchantDraft;
use App\ResultCode;
use Illuminate\Support\Facades\Session;

class OperAccountService extends BaseService
{

    /**
     * 运营中心账号登陆操作
     * @param $username
     * @param $password
     * @return OperAccount
     */
    public static function login($username,$password)
    {
        $user = OperAccount::where('account', $username)->first();
        if(empty($user)){
            throw new AccountNotFoundException();
        }
        if(OperAccount::genPassword($password, $user['salt']) != $user['password']){
            throw new PasswordErrorException();
        }
        if($user->status != 1){
            throw new NoPermissionException('帐号已被禁用');
        }
        // 检查运营中心是否被冻结
        $oper = OperService::getById($user->oper_id);
        if(empty($oper)){
            throw new DataNotFoundException('运营中心信息不存在');
        }
        if($oper->status != 1){
            throw new NoPermissionException('运营中心已被冻结');
        }

        // 将用户信息记录到session中
        session([
            config('oper.user_session') => $user,
        ]);

        $user->operName = $oper->name;

        return $user;
    }

    /**
     * 运营中心账号登出操作
     */
    public static function logout()
    {
        Session::forget(config('oper.user_session'));
    }

    /**
     * 修改密码操作
     * @param OperAccount $user
     * @param $password
     * @param $newPassword
     * @return OperAccount
     */
    public static function modifyPassword(OperAccount $user, $password, $newPassword)
    {

        if(OperAccount::genPassword($password, $user->salt) !== $user->password){
            throw new PasswordErrorException();
        }
        $user = self::editAccount($user->id, $user->oper_id, $newPassword);

        // 修改密码成功后更新session中的user
        session([
            config('oper.user_session') => $user,
        ]);

        $user->operName = Oper::where('id', $user->oper_id)->value('name');

        return $user;
    }


    /**
     * 获取运营中心菜单
     * @param int $operId
     * @return array
     */
    public static function getMenus($operId)
    {

        $oper = OperService::getById($operId);
        $merchantDraftCount = MerchantDraft::where('creator_oper_id', $operId)->count();

        $menus = [];

        if ($oper->pay_to_platform != Oper::PAY_TO_PLATFORM_WITH_SPLITTING) {

            //不是第三种模式不显示不显示超市相关的菜单
            $menus = [
                [ 'id' => 1, 'name' => '商户管理', 'level' => 1, 'url' => 'merchant', 'sub' =>
                    [
                        [ 'id' => 101, 'name' => '我的商户', 'level' => 2, 'url' => '/oper/merchants', 'pid' => 1,],
                        [ 'id' => 102, 'name' => '试点商户', 'level' => 2, 'url' => '/oper/merchant/pilots', 'pid' => 1,],
                        [ 'id' => 103, 'name' => '商户池', 'level' => 2, 'url' => '/oper/merchant/pool', 'pid' => 1,],
                        [ 'id' => 104, 'name' => '商户审核记录', 'level' => 2, 'url' => '/oper/merchant/audits', 'pid' => 1],
                        [ 'id' => 105, 'name' => '草稿箱('.$merchantDraftCount.')', 'level' => 2, 'url' => '/oper/merchant/drafts', 'pid' => 1],
                    ]

                ],
                [ 'id' => 3, 'name' => '订单管理', 'level' => 1, 'url' => 'orders', 'sub' =>
                    [
                        [ 'id' => 302, 'name' => '订单管理', 'level' => 2, 'url' => '/oper/orders', 'pid' => 2],
                    ]
                ],
                ['id' => 4, 'name' => '消息管理', 'level' => 1, 'url' => '', 'sub' =>
                    [
                        ['id' => 401, 'name' => '系统消息', 'level' => 2, 'url' => '/message/systems', 'pid' => 3],
                    ]
                ],
                [ 'id' => 5, 'name' => '人员管理', 'level' => 1, 'url' => 'user', 'sub' =>
                    [
//                    [ 'id' => 501, 'name' => '我的会员', 'level' => 2, 'url' => '/oper/invite/statistics/daily', 'pid' => 4,],
                        [ 'id' => 502, 'name' => '我的员工', 'level' => 2, 'url' => '/oper/operBizMembers', 'pid' => 4,]
                    ]
                ],
                [ 'id' => 6, 'name' => '业务员管理', 'level' => 1, 'url' => 'bizer', 'sub' =>
                    [
                        [ 'id' => 601, 'name' => '我的业务员', 'level' => 2, 'url' => '/oper/bizers', 'pid' => 5,],
                        [ 'id' => 602, 'name' => '业务员申请', 'level' => 2, 'url' => '/oper/bizerRecord', 'pid' => 5,],
                    ]
                ],

                [ 'id' => 7, 'name' => '账户管理', 'level' => 1, 'url' => '/wallet', 'sub' =>
                    [
                        [ 'id' => 701, 'name' => '账户总览', 'level' => 2, 'url' => '/oper/wallet/summary/list', 'pid' => 6,],
                        [ 'id' => 702, 'name' => '我的贡献值', 'level' => 2, 'url' => '/oper/wallet/consume/list', 'pid' => 6,],
//                    [ 'id' => 703, 'name' => '我的TPS积分', 'level' => 2, 'url' => '/oper/wallet/credit/list', 'pid' => 6,],
                        [ 'id' => 704, 'name' => '提现密码管理', 'level' => 2, 'url' => '/oper/wallet/withdraw/password', 'pid' => 6,],
                    ]
                ],

                [ 'id' => 8, 'name' => '财务管理', 'level' => 1, 'url' => '/oper/settlements',],
                [ 'id' => 9, 'name' => '用户管理', 'level' => 1, 'url' => 'member', 'sub' =>
                    [
                        [ 'id' => 901, 'name' => '我的用户', 'level' => 2, 'url' => '/member/index', 'pid' => 8,],
                        [ 'id' => 902, 'name' => '用户统计', 'level' => 2, 'url' => '/member/statistics', 'pid' => 8,],
                    ]
                ],
                [ 'id' => 10, 'name' => '推广渠道', 'level' => 1, 'url' => 'material', 'sub' =>
                    [
                        [ 'id' => 1001, 'name' => '渠道列表', 'level' => 2, 'url' => '/oper/invite-channel', 'pid' => 9,],
                    ]
                ],

//            [ 'id' => 11, 'name' => '系统配置', 'level' => 1, 'url' => 'sysconfig', 'sub' =>
//                [
//                    [ 'id' => 1101, 'name' => '绑定TPS', 'level' => 2, 'url' => '/oper/tps-bind', 'pid' => 10,],
//                ]
//            ],
                /*[ 'id' => 12, 'name' => '系统设置', 'level' => 1, 'url' => 'setting', 'sub' =>
                    [
                        [ 'id' => 1201, 'name' => '关联用户', 'level' => 2, 'url' => '/oper/setting/mapping_user', 'pid' => 11],
                    ]
                ],*/

            ];

        } else {
            $menus = [
                [ 'id' => 1, 'name' => '商户管理', 'level' => 1, 'url' => 'merchant', 'sub' =>
                    [
                        [ 'id' => 101, 'name' => '我的商户', 'level' => 2, 'url' => '/oper/merchants', 'pid' => 1,],
                        [ 'id' => 102, 'name' => '试点商户', 'level' => 2, 'url' => '/oper/merchant/pilots', 'pid' => 1,],
                        [ 'id' => 103, 'name' => '商户池', 'level' => 2, 'url' => '/oper/merchant/pool', 'pid' => 1,],
                        [ 'id' => 104, 'name' => '商户审核记录', 'level' => 2, 'url' => '/oper/merchant/audits', 'pid' => 1],
                        [ 'id' => 105, 'name' => '草稿箱('.$merchantDraftCount.')', 'level' => 2, 'url' => '/oper/merchant/drafts', 'pid' => 1],
                    ]

                ],
                [ 'id' => 2, 'name' => '超市商户管理', 'level' => 1, 'url' => 'cs/merchant', 'sub' =>
                    [
                        [ 'id' => 201, 'name' => '超市商户列表', 'level' => 2, 'url' => '/oper/cs/merchants', 'pid' => 2,],
                        [ 'id' => 202, 'name' => '添加商户', 'level' => 2, 'url' => '/oper/cs/merchant/add', 'pid' => 2,],
                        [ 'id' => 203, 'name' => '审核管理', 'level' => 2, 'url' => '/oper/cs/merchant/audit/list', 'pid' => 2],
                        [ 'id' => 204, 'name' => '超市商品管理', 'level' => 2, 'url' => '/oper/cs/goods', 'pid' => 2],
                    ]

                ],
                [ 'id' => 3, 'name' => '订单管理', 'level' => 1, 'url' => 'orders', 'sub' =>
                    [
                        [ 'id' => 301, 'name' => '超市订单管理', 'level' => 2, 'url' => '/oper/cs/orders', 'pid' => 3],
                        [ 'id' => 302, 'name' => '普通订单管理', 'level' => 2, 'url' => '/oper/orders', 'pid' => 3],
                    ]
                ],
                ['id' => 4, 'name' => '消息管理', 'level' => 1, 'url' => '', 'sub' =>
                    [
                        ['id' => 401, 'name' => '系统消息', 'level' => 2, 'url' => '/message/systems', 'pid' => 3],
                    ]
                ],
                [ 'id' => 5, 'name' => '人员管理', 'level' => 1, 'url' => 'user', 'sub' =>
                    [
//                    [ 'id' => 501, 'name' => '我的会员', 'level' => 2, 'url' => '/oper/invite/statistics/daily', 'pid' => 4,],
                        [ 'id' => 502, 'name' => '我的员工', 'level' => 2, 'url' => '/oper/operBizMembers', 'pid' => 4,]
                    ]
                ],
                [ 'id' => 6, 'name' => '业务员管理', 'level' => 1, 'url' => 'bizer', 'sub' =>
                    [
                        [ 'id' => 601, 'name' => '我的业务员', 'level' => 2, 'url' => '/oper/bizers', 'pid' => 5,],
                        [ 'id' => 602, 'name' => '业务员申请', 'level' => 2, 'url' => '/oper/bizerRecord', 'pid' => 5,],
                    ]
                ],

                [ 'id' => 7, 'name' => '账户管理', 'level' => 1, 'url' => '/wallet', 'sub' =>
                    [
                        [ 'id' => 701, 'name' => '账户总览', 'level' => 2, 'url' => '/oper/wallet/summary/list', 'pid' => 6,],
                        [ 'id' => 702, 'name' => '我的贡献值', 'level' => 2, 'url' => '/oper/wallet/consume/list', 'pid' => 6,],
//                    [ 'id' => 703, 'name' => '我的TPS积分', 'level' => 2, 'url' => '/oper/wallet/credit/list', 'pid' => 6,],
                        [ 'id' => 704, 'name' => '提现密码管理', 'level' => 2, 'url' => '/oper/wallet/withdraw/password', 'pid' => 6,],
                    ]
                ],

                [ 'id' => 8, 'name' => '财务管理', 'level' => 1, 'url' => '/oper/settlements',],
                [ 'id' => 9, 'name' => '用户管理', 'level' => 1, 'url' => 'member', 'sub' =>
                    [
                        [ 'id' => 901, 'name' => '我的用户', 'level' => 2, 'url' => '/member/index', 'pid' => 8,],
                        [ 'id' => 902, 'name' => '用户统计', 'level' => 2, 'url' => '/member/statistics', 'pid' => 8,],
                    ]
                ],
                [ 'id' => 10, 'name' => '推广渠道', 'level' => 1, 'url' => 'material', 'sub' =>
                    [
                        [ 'id' => 1001, 'name' => '渠道列表', 'level' => 2, 'url' => '/oper/invite-channel', 'pid' => 9,],
                    ]
                ],

//            [ 'id' => 11, 'name' => '系统配置', 'level' => 1, 'url' => 'sysconfig', 'sub' =>
//                [
//                    [ 'id' => 1101, 'name' => '绑定TPS', 'level' => 2, 'url' => '/oper/tps-bind', 'pid' => 10,],
//                ]
//            ],
                /*[ 'id' => 12, 'name' => '系统设置', 'level' => 1, 'url' => 'setting', 'sub' =>
                    [
                        [ 'id' => 1201, 'name' => '关联用户', 'level' => 2, 'url' => '/oper/setting/mapping_user', 'pid' => 11],
                    ]
                ],*/

            ];
        }

        return $menus;
    }

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
     * @return OperAccount
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