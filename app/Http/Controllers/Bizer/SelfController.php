<?php

namespace App\Http\Controllers\Bizer;

use App\Exceptions\AccountNotFoundException;
use App\Exceptions\BaseResponseException;
use App\Exceptions\PasswordErrorException;
use App\Exceptions\ParamInvalidException;
use App\Http\Controllers\Controller;
use App\Modules\Bizer\BizerService;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAccount;
use App\Modules\Bizer\Bizer;
use App\Modules\Sms\SmsVerifyCode;
use App\Modules\Sms\SmsVerifyCodeService;
use App\Result;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use App\ResultCode;

class SelfController extends Controller {

    /**
     * 登录
     * @author tong.chen
     */
    public function login() {
        $this->validate(request(), [
            'account' => 'required',
            'password' => 'required|between:6,30',
            'verifyCode' => 'required|captcha'
        ]);

        $user = Bizer::where('mobile', request('account'))->first();
        if (empty($user)) {
            throw new AccountNotFoundException();
        }
        if (Bizer::genPassword(request('password'), $user['salt']) != $user['password']) {
            throw new PasswordErrorException();
        }
        if ($user->status == Bizer::STATUS_OFF) {
            throw new BaseResponseException('该业务员已被禁用');
        }

        unset($user['password']);
        unset($user['salt']);

        session([
            config('bizer.user_session') => $user,
        ]);

        return Result::success([
                    'user' => $user,
                    'menus' => $this->getMenus(),
        ]);
    }

    /**
     * 注册
     * @author tong.chen
     */
    public function register() {
        $this->validate(request(), [
            'mobile' => 'required|regex:/^1[3,4,5,6,7,8,9]\d{9}/',
            'name' => 'required|max:10',
            'verify_code' => 'required|size:4',
            'password' => 'required|between:6,12',
            'confirmPassword' => 'required|same:password',
        ]);

        $mobile = request('mobile');
        $name = request('name');
        $password = request('password');
        $verifyCode = request('verify_code');

        $isExist = Bizer::where('mobile', $mobile)->first();
        if (!empty($isExist)) {
            throw new BaseResponseException('手机已存在', ResultCode::ACCOUNT_EXISTS);
        }

        $verifyCodeRes = SmsVerifyCodeService::checkVerifyCode($mobile, $verifyCode);
        if ($verifyCodeRes === FALSE) {
            throw new ParamInvalidException('验证码错误');
        }

        $bizer = new Bizer();
        $bizer->mobile = $mobile;
        $bizer->name = $name;
        $salt = str_random();
        $bizer->salt = $salt;
        $bizer->password = Bizer::genPassword($password, $salt);
        $bizer->save();

        unset($bizer['password']);
        unset($bizer['salt']);

        session([
            config('bizer.user_session') => $bizer,
        ]);

        return Result::success([
                    'user' => $bizer,
                    'menus' => $this->getMenus(),
        ]);
    }

    public function logout() {
        Session::forget(config('bizer.user_session'));
        return Result::success();
    }

    public function modifyPassword() {
        $this->validate(request(), [
            'password' => 'required',
            'newPassword' => 'required|between:6,30',
            'reNewPassword' => 'required|same:newPassword'
        ]);

        $user = request()->get('current_user');
        // 检查原密码是否正确
        if (MerchantAccount::genPassword(request('password'), $user->salt) !== $user->password) {
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

        $user->merchantName = Merchant::where('id', $user->merchant_id)->value('name');

        return Result::success($user);
    }

    /**
     * 忘记密码
     * @author tong.chen
     */
    public function forgotPassword() {
        $this->validate(request(), [
            'mobile' => 'required|regex:/^1[3,4,5,6,7,8,9]\d{9}/',
            'verify_code' => 'required|size:4'
        ]);

        $mobile = request('mobile');
        $verifyCode = request('verify_code');
        $password = request('password');
        $confirmPassword = request('confirm_password');

        $bizer = Bizer::where('mobile', $mobile)->first();
        if (empty($bizer)) {
            throw new AccountNotFoundException();
        }
        
        if (!($password && $confirmPassword)) {
            if (App::environment('production') || $verifyCode != '6666') {
                $verifyCodeRecord = SmsVerifyCode::where('mobile', $mobile)
                        ->where('verify_code', $verifyCode)
                        ->where('status', 1)
                        ->where('expire_time', '>', Carbon::now())
                        ->first();
                if (empty($verifyCodeRecord)) {
                    throw new ParamInvalidException('验证码错误');
                }
                return Result::success();
            }
            return Result::success();
        }

        $this->validate(request(), [
            'password' => 'required|between:6,12',
            'confirm_password' => 'required|same:password',
        ]);
        
        $verifyCodeRes = SmsVerifyCodeService::checkVerifyCode($mobile, $verifyCode);
        if ($verifyCodeRes === FALSE) {
            throw new ParamInvalidException('验证码错误');
        }
        
        $salt = str_random();
        $bizer->salt = $salt;
        $bizer->password = Bizer::genPassword($password, $salt);
        $bizer->save();
        
        return Result::success();
    }

    private function getMenus() {
        return [
            ['id' => 1, 'name' => '订单管理', 'level' => 1, 'url' => '/bizer/orders', 'sub' =>
                [
                    ['id' => 10, 'name' => '订单列表', 'level' => 2, 'url' => '/bizer/orders', 'pid' => 1],
                ]
            ],
            ['id' => 2, 'name' => '商户管理', 'level' => 1, 'url' => '/bizer/merchants', 'sub' =>
                [
                    ['id' => 20, 'name' => '商户列表', 'level' => 2, 'url' => '/bizer/merchants', 'pid' => 2],
                ]
            ],
            ['id' => 3, 'name' => '运营中心管理', 'level' => 1, 'url' => '/bizer/opers', 'sub' =>
                [
                    ['id' => 30, 'name' => '运营中心列表', 'level' => 2, 'url' => '/bizer/opers', 'pid' => 3],
                    ['id' => 31, 'name' => '申请记录', 'level' => 2, 'url' => '/bizer/opersRecord', 'pid' => 3],
                ]
            ],
            ['id' => 4, 'name' => '财务管理', 'level' => 1, 'url' => '/bizer/wallet', 'sub' =>
                [
                    ['id' => 40, 'name' => '财务总览', 'level' => 2, 'url' => '/bizer/wallet/bills', 'pid' => 4],
                ]
            ],
            ['id' => 6, 'name' => '消息管理', 'level' => 1, 'url' => '', 'sub' =>
                [
                    ['id' => 60, 'name' => '系统消息', 'level' => 2, 'url' => '/bizer/message/systems', 'pid' => 6],
                ]
            ],
            ['id' => 5, 'name' => '设置', 'level' => 1, 'url' => '/bizer/withdraw', 'sub' =>
                [
                    ['id' => 50, 'name' => '提现设置', 'level' => 2, 'url' => '/bizer/withdraw/password/form', 'pid' => 5],
                ]
            ],
        ];
    }

    /**
     * 修改业务员昵称
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeName()
    {
        $bizer = request()->get('current_user');
        if (empty($bizer)) {
            throw new BaseResponseException('请先登录');
        }
        $this->validate(request(), [
            'name' => 'required|max:10'
        ]);
        $id = $bizer->id;
        $name = request('name');
        $bizer = BizerService::changeName($id, $name);

        return Result::success($bizer);
    }

}
