<?php

namespace App\Modules\Merchant;

use App\BaseService;
use App\Exceptions\PasswordErrorException;
use App\Exceptions\AccountNotFoundException;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\NoPermissionException;
use App\Modules\Tps\TpsBind;
use App\Modules\Tps\TpsBindService;

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
        $merchant = MerchantService::getById($user->merchant_id);
        if(empty($merchant)){
            throw new DataNotFoundException('商户信息不存在');
        }
        if($merchant->status != 1){
            throw new NoPermissionException('商户已被冻结');
        }

        session([
            config('merchant.user_session') => $user,
        ]);

        $user->merchantName = $merchant->name;

        return $user;
    }

    /**
     * @param $operId
     * @return array
     */
    public static function getMenus($operId){

        $menus =  (new self())->Menus();

        $operBindInfo = TpsBindService::getTpsBindInfoByOriginInfo($operId, TpsBind::ORIGIN_TYPE_OPER);
        if(empty($operBindInfo)){
            // 如果商户所属运营中心没有绑定tps帐号, 则去掉商户的绑定tps帐号菜单
            foreach ($menus as $key => &$second) {
                if(isset($second['sub'])){
                    foreach ($second['sub'] as $key2 => $sub) {
                        if($sub['name'] == 'TPS会员账号管理'){
                            unset($menus[$key]['sub'][$key2]);
                        }
                    }
                }
            }
        }

        return $menus;
    }

    /**
     * @return array
     */
    public function Menus()
    {
        $menus = [
            [ 'id' => 1, 'name' => '商品管理', 'level' => 1, 'url' => 'goods', 'sub' =>
                [
                    ['id' => 9, 'name' => '团购商品', 'level' => 2, 'url' => '/merchant/goods', 'pid' => 1],
                    ['id' => 10, 'name' => '单品分类', 'level' => 2, 'url' => '/merchant/dishesCategories', 'pid' => 1],
                    ['id' => 11, 'name' => '单品管理', 'level' => 2, 'url' => '/merchant/dishesGoods', 'pid' => 1],
                ]
            ],
            [ 'id' => 2, 'name' => '订单管理', 'level' => 1, 'url' => '/merchant/orders',],

            [ 'id' => 3, 'name' => '会员管理', 'level' => 1, 'url' => 'user', 'sub' =>
                [
                    [ 'id' => 4, 'name' => '我的会员', 'level' => 2, 'url' => '/merchant/invite/statistics/list', 'pid' => 3,],
                    [ 'id' => 13, 'name' => '会员分析', 'level' => 2, 'url' => '/merchant/invite/statistics/daily', 'pid' => 3,],
                ]
            ],
            [ 'id' => 5, 'name' => '财务管理', 'level' => 1, 'url' => '/merchant/settlements',],
            [ 'id' => 6, 'name' => '素材中心', 'level' => 1, 'url' => 'material', 'sub' =>
                [
                    [ 'id' => 7, 'name' => '分享会员二维码', 'level' => 2, 'url' => '/merchant/invite/channel', 'pid' => 6,],
                    [ 'id' => 8, 'name' => '支付二维码', 'level' => 2, 'url' => '/merchant/pay/qrcode', 'pid' => 6,],
                ]
            ],
            [ 'id' => 9, 'name' => '系统设置', 'level' => 1, 'url' => 'setting', 'sub' =>
                [
//                    [ 'id' => 10, 'name' => '关联用户', 'level' => 2, 'url' => '/merchant/setting/mapping_user', 'pid' => 9],
                    [ 'id' => 12, 'name' => '系统配置', 'level' => 2, 'url' => '/merchant/setting', 'pid' => 9 ],
                    [ 'id' => 13, 'name' => 'TPS会员账号管理', 'level' => 2, 'url' => '/merchant/tps-bind', 'pid' => 9 ],
                ]
            ],
        ];
        return $menus;
    }

    /**
     * @param $user
     * @param $password
     * @param $newPassword
     * @return MerchantAccount|mixed
     */
    public static function modifyPassword($user,$password,$newPassword){

        // 检查原密码是否正确
        if(MerchantAccount::genPassword($password, $user->salt) !== $user->password){
            throw new PasswordErrorException();
        }
        $user = MerchantAccount::findOrFail($user->id);
        $salt = str_random();
        $user->salt = $salt;
        $user->password = MerchantAccount::genPassword($newPassword, $salt);
        $user->save();

        // 修改密码成功后更新session中的user
        session([
            config('merchant.user_session') => $user,
        ]);

        $user->merchantName = MerchantService::getNameById($user->merchant_id, ['name']);

        return $user;
    }

    /**
     * @param $merchantId
     * @return Merchant
     */
    public static function getMerchantInfo($merchantId){

        $merchant = MerchantService::getById($merchantId,
            ['id','name','signboard_name','merchant_category_id','province','city','area','address','desc']
        );

        $mc = MerchantCategory::where('id',$merchant->merchant_category_id)->first(['name','pid']);

        if($mc){
            //父类别
            $merchant->merchantCategoryName = $mc->name;
            while ($mc->pid != 0){
                $mc = MerchantCategory::where('id',$mc->pid)->first(['name','pid']);
                $merchant->merchantCategoryName =  $mc->name . ' ' .$merchant->merchantCategoryName;
            }
        }else{
            $merchant->merchantCategoryName = '';
        }

        $merchant->signboardName = $merchant->signboard_name;
        unset($merchant->merchant_category_id);
        unset($merchant->signboard_name);

        return $merchant;
    }
}
