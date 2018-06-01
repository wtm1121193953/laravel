<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/6/1
 * Time: 21:01
 */

namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Modules\Merchant\Merchant;
use App\Modules\Setting\SettingService;
use App\Modules\User\User;
use App\Modules\UserCredit\UserCreditSettingService;
use App\Result;

class CreditController extends Controller
{
    public function payAmountToCreditRatio()
    {
        $this->validate(request(), [
            'merchantId' => 'required'
        ]);

        $merchantId = request('merchantId');
        $user = request()->get('current_user');

        // 获取商户的返利比例(即盈利比例)
        $settlementRate = Merchant::where('id', $merchantId)->value('settlement_rate'); //分利比例
        if (!isset($user->level) || empty($user->level)){
            $userLevel = User::where('id', $user->id)->value('level'); //用户等级
        }else{
            $userLevel = $user->level;
        }
        $creditRatio = UserCreditSettingService::getCreditToSelfRatioSetting($userLevel); //自反比例
        $creditMultiplierOfAmount = SettingService::getValueByKey('credit_multiplier_of_amount'); //积分系数
        $creditRatio = $settlementRate * $creditRatio * $creditMultiplierOfAmount / 100 ; //积分换算比例

        return Result::success([
            'creditRatio' => $creditRatio,
        ]);
    }
}