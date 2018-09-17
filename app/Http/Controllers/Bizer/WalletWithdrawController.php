<?php

namespace App\Http\Controllers\Bizer;

use App\Exceptions\BaseResponseException;
use App\Http\Controllers\Controller;
use App\Modules\Bizer\BizerService;
use App\Modules\Wallet\BankCard;
use App\Modules\Wallet\BankCardService;
use App\Modules\Wallet\WalletService;
use App\Result;
use Illuminate\Support\Facades\DB;

class WalletWithdrawController extends Controller
{
    /**
     * 业务员提现设置
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function withdrawSetting()
    {
        $this->validate(request(), [
            'bank_card_no' => 'required|numeric|min:10000000|max:999999999999999999999999999999|unique:bank_cards',
            'bank_card_open_name' => 'required|max:20',
            'bank_name' => 'required|max:20',
            'id_card_name' => 'required|max:20',
            'id_card_no' => 'required|identitycards|unique:user_identity_audit_records',
            'front_pic' => 'required',
            'opposite_pic' => 'required',
            'password' => 'required|max:6',
            'checkPassword' => 'required|max:6|same:password'
        ]);

        $bankCardNo = request('bank_card_no');
        $bankCardOpenName = request('bank_card_open_name');
        $bankName = request('bank_name');

        $idCardName = request('id_card_name');
        $idCardNo = request('id_card_no');
        $frontPic = request('front_pic');
        $oppositePic = request('opposite_pic');

        $password = request('password');

        $bizer = request()->get('current_user');

        DB::beginTransaction();
        try {
            // 1.业务员添加审核
            BizerService::addBizerIdentityAuditRecord(compact('idCardName', 'idCardNo', 'frontPic', 'oppositePic'), $bizer);

            // 2.添加银行卡信息
            $saveData = [
                'bank_card_no' => $bankCardNo,
                'bank_card_open_name' => $bankCardOpenName,
                'bank_name' => $bankName,
            ];
            BankCardService::addCard($saveData, $bizer, BankCard::ORIGIN_TYPE_BIZER);

            // 3.设置提现密码
            $wallet = WalletService::getWalletInfo($bizer);
            WalletService::updateWalletWithdrawPassword($wallet, $password);

            DB::commit();

            return Result::success();
        } catch (\Exception $e) {
            DB::rollBack();

            $msg = $e->getResponse()->original['message'] ?: '提现设置失败';
            throw new BaseResponseException($msg);
        }
    }
}