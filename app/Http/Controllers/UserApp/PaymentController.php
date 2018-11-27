<?php

namespace App\Http\Controllers\UserApp;

use App\Modules\Payment\Payment;
use App\Modules\Payment\PaymentService;
use App\Modules\User\UserIdentityAuditRecord;
use App\Modules\User\UserIdentityAuditRecordService;
use App\Modules\Wallet\WalletService;
use App\Result;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function getListByPlatform(Request $request)
    {
        // 查询字段
        $whereArr = [
            'on_pc' =>  null,
            'on_miniprogram'    => null,
            'on_app'    =>  null,
            'type'      =>  null
        ];
        $whereArr['on_app'] = Payment::APP_ON;
        $wallet = WalletService::getWalletInfo($request->get('current_user'))->toArray();
        $list = PaymentService::getListByPlatForm(array_filter($whereArr),$wallet);
        $record = UserIdentityAuditRecordService::getRecordByUserId($request->get('current_user')->id);
        $wallet['identityInfoStatus'] = ($record) ? $record->status : UserIdentityAuditRecord::STATUS_UN_SAVE;

        return Result::success(['list'=>$list,'wallet'=>$wallet]);
    }
}
