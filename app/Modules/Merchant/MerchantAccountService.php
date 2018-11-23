<?php

namespace App\Modules\Merchant;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\PasswordErrorException;
use App\Exceptions\AccountNotFoundException;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\NoPermissionException;
use App\Modules\Cs\CsMerchantService;
use App\Modules\Oper\Oper;
use App\Modules\Oper\OperService;
use App\Modules\Tps\TpsBind;
use App\Modules\Tps\TpsBindService;
use Illuminate\Support\Facades\Session;

class MerchantAccountService extends BaseService
{

    /**
     * @param $username
     * @param $password
     * @return MerchantAccount
     */
    public static function login($username,$password){

        $user = MerchantAccount::where('account', $username)->first();
        if(empty($user)){
            throw new AccountNotFoundException();
        }
        if(MerchantAccount::genPassword($password, $user['salt']) != $user['password']){
            throw new PasswordErrorException();
        }
        if($user->status != 1){
            throw new NoPermissionException('帐号已被禁用');
        }
        if($user->type == MerchantAccount::TYPE_NORMAL){

            $merchant = MerchantService::getById($user->merchant_id);
            if(empty($merchant)){
                throw new DataNotFoundException('商户信息不存在');
            }
            if($merchant->status != 1){
                throw new NoPermissionException('商户已被冻结');
            }
            $user->merchantName = $merchant->name;
        }else {
            // 大千超市信息获取
            $csMerchant =  CsMerchantService::getById($user->merchant_id);
            if(empty($csMerchant)){
                throw new DataNotFoundException('商户信息不存在');
            }
            if($csMerchant->status != 1){
                throw new NoPermissionException('商户已被冻结');
            }
            $user->merchantName = $csMerchant->name;
        }

        // 将用户信息记录到session中
        session([
            config('merchant.user_session') => $user,
        ]);


        return $user;
    }

    /**
     * 退出登陆操作
     */
    public static function logout()
    {
        Session::forget(config('merchant.user_session'));
    }

    /**
     * @param MerchantAccount $user
     * @return array
     */
    public static function getMenus(MerchantAccount $user){

        $menus = self::menus($user);

        if($user->type == MerchantAccount::TYPE_NORMAL){
            $operId = $user->oper_id;
            // 查询运营中心是否绑定tps账号
            $operBindInfo = TpsBindService::getTpsBindInfoByOriginInfo($operId, TpsBind::ORIGIN_TYPE_OPER);
            $operUnbindTps = empty($operBindInfo);
            // 查询运营中心信息是否绑定到平台
            $oper = OperService::getById($operId, 'pay_to_platform');
            $isPayToPlatform = in_array($oper->pay_to_platform, [Oper::PAY_TO_PLATFORM_WITHOUT_SPLITTING, Oper::PAY_TO_PLATFORM_WITH_SPLITTING]);
            if($operUnbindTps || !$isPayToPlatform){
                foreach ($menus as $key => &$second) {
                    if(isset($second['sub'])){
                        foreach ($second['sub'] as $key2 => $sub) {
                            // 如果商户所属运营中心没有绑定tps帐号, 则去掉商户的绑定tps帐号菜单
                            if($operUnbindTps && $sub['name'] == 'TPS会员账号管理'){
                                unset($menus[$key]['sub'][$key2]);
                            }
                            if(!$isPayToPlatform && $sub['name'] == '平台结算管理'){
                                unset($menus[$key]['sub'][$key2]);
                            }
                        }
                    }
                    if (!$isPayToPlatform && $second['name'] == '电子合同管理') {
                        array_splice($menus, $key, 1);
                    }
                }
            }
        }
        return $menus;
    }

    /**
     * @param MerchantAccount $user
     * @return array
     */
    public static function menus(MerchantAccount $user)
    {
        if($user->type == MerchantAccount::TYPE_NORMAL){
            $menus = [
                [ 'id' => 1, 'name' => '商品管理', 'level' => 1, 'url' => 'goods', 'sub' =>
                    [
                        ['id' => 9, 'name' => '团购商品', 'level' => 2, 'url' => '/merchant/goods', 'pid' => 1],
                        ['id' => 10, 'name' => '单品分类', 'level' => 2, 'url' => '/merchant/dishesCategories', 'pid' => 1],
                        ['id' => 11, 'name' => '单品管理', 'level' => 2, 'url' => '/merchant/dishesGoods', 'pid' => 1],
                    ]
                ],
                [ 'id' => 2, 'name' => '订单管理', 'level' => 1, 'url' => '/merchant/orders',],
                ['id' => 61, 'name' => '消息管理', 'level' => 1, 'url' => '', 'sub' =>
                    [
                        ['id' => 60, 'name' => '公告', 'level' => 2, 'url' => '/message/systems', 'pid' => 61],
                    ]
                ],
                [ 'id' => 3, 'name' => '用户管理', 'level' => 1, 'url' => 'user', 'sub' =>
                    [
                        [ 'id' => 4, 'name' => '我的用户', 'level' => 2, 'url' => '/merchant/invite/statistics/list', 'pid' => 3,],
                        [ 'id' => 13, 'name' => '用户分析', 'level' => 2, 'url' => '/merchant/invite/statistics/daily', 'pid' => 3,],
                    ]
                ],
                [ 'id' => 5, 'name' => '财务管理', 'level' => 1, 'url' => '/merchant/settlements',
                    'sub' => [
                        [ 'id' => 19, 'name' => '运营中心结算管理', 'level' => 2, 'url' => '/merchant/settlements', 'pid' => 5,],
                        [ 'id' => 20, 'name' => '平台结算管理', 'level' => 2, 'url' => '/merchant/settlement/platform/list', 'pid' => 5,],
                    ]
                ],
                [ 'id' => 15, 'name' => '账户管理', 'level' => 1, 'url' => '/wallet', 'sub' =>
                    [
                        [ 'id' => 16, 'name' => '账户总览', 'level' => 2, 'url' => '/merchant/wallet/summary/list', 'pid' => 15,],
                        [ 'id' => 17, 'name' => '我的贡献值', 'level' => 2, 'url' => '/merchant/wallet/consume/list', 'pid' => 15,],
//                    [ 'id' => 19, 'name' => '我的TPS积分', 'level' => 2, 'url' => '/merchant/wallet/credit/list', 'pid' => 15,],
                        [ 'id' => 18, 'name' => '提现密码管理', 'level' => 2, 'url' => '/merchant/wallet/withdraw/password', 'pid' => 15,],
                    ]
                ],
                [ 'id' => 6, 'name' => '素材中心', 'level' => 1, 'url' => 'material', 'sub' =>
                    [
                        [ 'id' => 7, 'name' => '分享用户二维码', 'level' => 2, 'url' => '/merchant/invite/channel', 'pid' => 6,],
                        [ 'id' => 8, 'name' => '支付二维码', 'level' => 2, 'url' => '/merchant/pay/qrcode', 'pid' => 6,],
                    ]
                ],
                [ 'id' => 21, 'name' => '电子合同管理', 'level' => 1, 'url' => '/merchant/electronic/contract',],
                [ 'id' => 9, 'name' => '系统设置', 'level' => 1, 'url' => 'setting', 'sub' =>
                    [
//                    [ 'id' => 10, 'name' => '关联用户', 'level' => 2, 'url' => '/merchant/setting/mapping_user', 'pid' => 9],
                        [ 'id' => 12, 'name' => '系统配置', 'level' => 2, 'url' => '/merchant/setting', 'pid' => 9 ],
//                    [ 'id' => 14, 'name' => 'TPS会员账号管理', 'level' => 2, 'url' => '/merchant/tps-bind', 'pid' => 9 ],
                    ]
                ],
            ];
        }else {
            $menus = [
                [ 'id' => 1, 'name' => '商品管理', 'level' => 1, 'url' => 'goods', 'sub' =>
                    [
                        ['id' => 9, 'name' => '商品管理', 'level' => 2, 'url' => '/cs/goods', 'pid' => 1],
                        ['id' => 10, 'name' => '商品分类管理', 'level' => 2, 'url' => '/cs/categories', 'pid' => 1],
                    ]
                ],
                [ 'id' => 2, 'name' => '订单管理', 'level' => 1, 'url' => 'order', 'sub' =>
                    [
                        ['id' => 201, 'name' => '超市订单管理', 'level' => 2, 'url' => '/cs/orders', 'pid' => 2],
                        ['id' => 202, 'name' => '买单订单管理', 'level' => 2, 'url' => '/cs/scan/orders', 'pid' => 2],
                    ]
                ],
                ['id' => 3, 'name' => '消息管理', 'level' => 1, 'url' => '', 'sub' =>
                    [
                        ['id' => 301, 'name' => '公告', 'level' => 2, 'url' => '/cs/message/systems', 'pid' => 61],
                    ]
                ],
                [ 'id' => 4, 'name' => '用户管理', 'level' => 1, 'url' => 'user', 'sub' =>
                    [
                        [ 'id' => 401, 'name' => '我的用户', 'level' => 2, 'url' => '/cs/invite/statistics/list', 'pid' => 3,],
                        [ 'id' => 402, 'name' => '用户分析', 'level' => 2, 'url' => '/cs/merchant/invite/statistics/daily', 'pid' => 3,],
                    ]
                ],
                [ 'id' => 5, 'name' => '财务管理', 'level' => 1, 'url' => 'settlements',
                    'sub' => [
                        [ 'id' => 501, 'name' => '平台结算管理', 'level' => 2, 'url' => '/cs/settlement/platform/list', 'pid' => 5,],
                    ]
                ],
                [ 'id' => 6, 'name' => '账户管理', 'level' => 1, 'url' => '/wallet', 'sub' =>
                    [
                        [ 'id' => 601, 'name' => '账户总览', 'level' => 2, 'url' => '/cs/wallet/summary/list', 'pid' => 15,],
                        [ 'id' => 602, 'name' => '我的贡献值', 'level' => 2, 'url' => '/cs/wallet/consume/list', 'pid' => 15,],
                        [ 'id' => 603, 'name' => '提现密码管理', 'level' => 2, 'url' => '/cs/wallet/withdraw/password', 'pid' => 15,],
                    ]
                ],
                [ 'id' => 7, 'name' => '素材中心', 'level' => 1, 'url' => 'material', 'sub' =>
                    [
                        [ 'id' => 701, 'name' => '分享用户二维码', 'level' => 2, 'url' => '/cs/invite/channel', 'pid' => 6,],
                        [ 'id' => 702, 'name' => '支付二维码', 'level' => 2, 'url' => '/cs/pay/qrcode', 'pid' => 6,],
                    ]
                ],
                [ 'id' => 8, 'name' => '电子合同管理', 'level' => 1, 'url' => '/cs/electronic/contract',],
                [ 'id' => 9, 'name' => '配送设置', 'level' => 1, 'url' => '/cs/setting/delivery',],
                [ 'id' => 10, 'name' => '系统设置', 'level' => 1, 'url' => 'setting', 'sub' =>
                    [
                        [ 'id' => 1001, 'name' => '系统配置', 'level' => 2, 'url' => '/cs/setting', 'pid' => 9 ],
                    ]
                ],
            ];
        }
        return $menus;
    }

    /**
     * @param $user
     * @param $password
     * @param $newPassword
     * @return MerchantAccount|mixed
     */
    public static function modifyPassword(MerchantAccount $user,$password,$newPassword){

        // 检查原密码是否正确
        if(MerchantAccount::genPassword($password, $user->salt) !== $user->password){
            throw new PasswordErrorException();
        }
        $user = self::editAccount($user->id, $newPassword);

        // 修改密码成功后更新session中的user
        session([
            config('merchant.user_session') => $user,
        ]);

        $user->merchantName = MerchantService::getNameById($user->merchant_id, ['name']);

        return $user;
    }

    /**
     * 创建商户账号
     * @param $merchantId
     * @param $getAccount
     * @param $operId
     * @param $password
     * @return MerchantAccount
     */
    public static function createAccount($merchantId, $getAccount,$operId,$password){

        $isAccount = MerchantAccount::where('merchant_id', $merchantId)->first();
        if(!empty($isAccount)){
            throw new BaseResponseException('该商户账户已存在, 不能重复创建');
        }
        // 查询账号是否重复
        if(!empty(MerchantAccount::where('account', $getAccount)->first())){
            throw new BaseResponseException('帐号重复, 请更换帐号');
        }

        $account = new MerchantAccount();
        $account->oper_id = $operId;
        $account->account = $getAccount;
        $account->merchant_id = $merchantId;
        $salt = str_random();
        $account->salt = $salt;
        $account->password = MerchantAccount::genPassword($password, $salt);
        $account->save();

        return $account;
    }

    /**
     * 辑商户账号信息, 即修改密码
     * @param $id
     * @param $password
     * @return MerchantAccount
     */
    public static function editAccount($id,$password){
        $account = MerchantAccount::findOrFail($id);
        $salt = str_random();
        $account->salt = $salt;
        $account->password = MerchantAccount::genPassword($password, $salt);

        $account->save();

        return $account;
    }

}
