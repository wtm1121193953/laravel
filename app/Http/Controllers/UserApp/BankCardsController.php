<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/1
 * Time: 下午2:07
 */

namespace App\HTTP\Controllers\UserApp;

use App\Http\Controllers\Controller;
use App\Result;
use App\Validator\Wallet\BankCard;
use Illuminate\Http\Request;
use App\Modules\Wallet\BankCardService;
use App\ResultCode;

class BankCardsController extends Controller
{
    public $validator;

    public function __construct()
    {
        $this->validator = new BankCard;
    }

    /**
     * 添加银行卡
     * Author:  zwg
     * Date:    180831
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     * @return 添加成功失败信息
     */
    public function addCard(Request $request)
    {
        $data = ['bank_card_open_name' => request('bank_card_open_name'), 'bank_card_no' => request('bank_card_no'), 'bank_name' => request('bank_name')];
        $bankCardNo = $data['bank_card_no'];
        $bankCardInfo = BankCardService::getCardByBankCardNo($bankCardNo);
        if ($bankCardInfo) {
            return Result::error(ResultCode::DB_INSERT_FAIL, '已添加该银行卡号');
        }
        BankCardService::addCard($data, request()->get('current_user'));

        return Result::success('添加银行卡成功');
    }

    /**
     * 设置默认银行卡
     * Author:  zwg
     * Date:    180831
     */
    public function changDefault()
    {
        $data = ['id' => request('id')];
        BankCardService::changeDefault($data,request()->get('current_user'));
        return Result::success('修改默认银行卡成功');
    }

    /**
     * 删除银行卡
     * Author:  zwg
     * Date:    180831
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delCard()
    {
        $data = ['id' => request('id')];

        BankCardService::delCard($data,request()->get('current_user'));
        return Result::success('删除银行卡成功');
    }

    /**
     * 获取银行卡列表
     * Author:  zwg
     * Date:    180831
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getCardsList(Request $request)
    {
        $bankCard = new \App\Modules\Wallet\BankCard;
        $currentUser = $request->get('current_user');
        $list = $bankCard::where('origin_id', $currentUser->id)
            ->where('origin_type', $currentUser->status)
            ->orderBy('default', 'desc')
            ->get();
        return Result::success($list);
    }

    /**
     * 获取用户origin_id
     */
    private function getUserId()
    {
        $user = request()->get('current_user');
        $value = empty($user->id) ? '' : $user->id;
        return $value;
    }
}