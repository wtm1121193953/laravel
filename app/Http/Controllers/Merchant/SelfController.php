<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/14
 * Time: 14:58
 */

namespace App\Http\Controllers\Merchant;


use App\Exceptions\AccountNotFoundException;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\NoPermissionException;
use App\Exceptions\PasswordErrorException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantAccount;
use App\Modules\Merchant\MerchantCategory;
use App\Modules\Merchant\MerchantService;
use App\Modules\Tps\TpsBind;
use App\Modules\Tps\TpsBindService;
use App\Result;
use Illuminate\Support\Facades\Session;

class SelfController extends Controller
{

    public function login()
    {
        $this->validate(request(), [
            'username' => 'required',
            'password' => 'required|between:6,30',
            'verifyCode' => 'required|captcha'
        ]);
        $user = MerchantAccount::where('account', request('username'))->first();
        if(empty($user)){
            throw new AccountNotFoundException();
        }
        if(MerchantAccount::genPassword(request('password'), $user['salt']) != $user['password']){
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

        $menus = $this->getMenus();

        $operBindInfo = TpsBindService::getTpsBindInfoByOriginInfo($user->oper_id, TpsBind::ORIGIN_TYPE_OPER);
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

        return Result::success([
            'user' => $user,
            'menus' => $menus,
        ]);
    }

    public function logout()
    {
        Session::forget(config('merchant.user_session'));
        return Result::success();
    }

    public function modifyPassword()
    {
        $this->validate(request(), [
            'password' => 'required',
            'newPassword' => 'required|between:6,30',
            'reNewPassword' => 'required|same:newPassword'
        ]);
        $user = request()->get('current_user');
        // 检查原密码是否正确
        if(MerchantAccount::genPassword(request('password'), $user->salt) !== $user->password){
            throw new PasswordErrorException();
        }
        $user = MerchantAccount::findOrFail($user->id);
        $salt = str_random();
        $user->salt = $salt;
        $user->password = MerchantAccount::genPassword(request('newPassword'), $salt);
        $user->save();

        // 修改密码成功后更新session中的user
        session([
            config('merchant.user_session') => $user,
        ]);

        $user->merchantName = MerchantService::getNameById($user->merchant_id, ['name']);

        return Result::success($user);
    }

    private function getMenus()
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
     * 获取商户信息
     */
    public function getMerchantInfo(){
        $merchant = MerchantService::getById(
            request()->get('current_user')->merchant_id,
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

        return Result::success($merchant);
    }
}