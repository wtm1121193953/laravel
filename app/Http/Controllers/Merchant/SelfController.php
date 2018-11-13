<?php

namespace App\Http\Controllers\Merchant;

use App\Exceptions\UnloginException;
use App\Http\Controllers\Controller;
use App\Modules\Merchant\MerchantAccountService;
use App\Modules\Merchant\MerchantElectronicContractService;
use App\Modules\Merchant\MerchantService;
use App\Result;
use Carbon\Carbon;

class SelfController extends Controller
{

    /**
     * 登录
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function login()
    {
        $this->validate(request(), [
            'username' => 'required',
            'password' => 'required|between:6,30',
            'verifyCode' => 'required|captcha'
        ]);

        $username = request('username');
        $password = request('password');

        $user = MerchantAccountService::login($username,$password);
        $menus = MerchantAccountService::getMenus($user->oper_id);

        return Result::success([
            'user' => $user,
            'menus' => $menus,
        ]);
    }

    public function getMenus()
    {
        $user = request()->get('current_user');
        if(empty($user)){
            throw new UnloginException();
        }

        $menus = MerchantAccountService::getMenus($user->oper_id);

        return Result::success([
            'user' => $user,
            'menus' => $menus,
        ]);
    }

    /**
     * 登出
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function logout()
    {
        MerchantAccountService::logout();
        return Result::success();
    }

    /**
     * 修改密码
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function modifyPassword()
    {
        $this->validate(request(), [
            'password' => 'required',
            'newPassword' => 'required|between:6,30',
            'reNewPassword' => 'required|same:newPassword'
        ]);
        $user = request()->get('current_user');
        $password = request('password');
        $newPassword = request('newPassword');

        $user = MerchantAccountService::modifyPassword($user,$password,$newPassword);

        return Result::success($user);
    }


    /**
     * 获取商户信息
     */
    public function getMerchantInfo(){
        $merchantId = request()->get('current_user')->merchant_id;
        $merchant = MerchantService::detail($merchantId);

        return Result::success($merchant);
    }

    /**
     * 商户检查电子合同
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkElectronicContract()
    {
        $merchantId = request()->get('current_user')->merchant_id;
        $contract = MerchantElectronicContractService::getContractByMerchantId($merchantId);
        if (!empty($contract)) {
            // 1-未过期  0-已过期
            $contract->status = ($contract->expiry_time > Carbon::now()) ? 1 : 0;
        }

        return Result::success($contract);
    }

    public function getMerchantAndElectronicContract()
    {
        $merchantId = request()->get('current_user')->merchant_id;
        $contract = MerchantElectronicContractService::getContractByMerchantId($merchantId);
        if (empty($contract)) {
            $contract = MerchantElectronicContractService::createdElectronicContract($merchantId);
        }
        $merchant = MerchantService::detail($merchantId);

        return Result::success([
            'contract' => $contract,
            'merchant' => $merchant,
            'nowTime' => Carbon::now(),
        ]);
    }

    /**
     * 签约或续约合同
     * @return \Illuminate\Http\JsonResponse
     */
    public function signElectronicContract()
    {
        $merchantId = request()->get('current_user')->merchant_id;
        $contract = MerchantElectronicContractService::getContractByMerchantId($merchantId);
        if (empty($contract)) {
            $contract = MerchantElectronicContractService::createdElectronicContract($merchantId);
            $contract = MerchantElectronicContractService::updateElectronicContract($contract->id);
        } elseif (!$contract->expiry_time || $contract->expiry_time < Carbon::now()) {
            $contract = MerchantElectronicContractService::updateElectronicContract($contract->id);
        } else {
            return Result::success($contract);
        }

        return Result::success($contract);
    }

    public function showElectronicContract()
    {
        $merchantId = request()->get('current_user')->merchant_id;
        $contract = MerchantElectronicContractService::getContractByMerchantId($merchantId);
    }
}