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
use App\Support\BankCards;
use App\Validator\Wallet\BankCard;
use Illuminate\Http\Request;
use App\Modules\Wallet\BankCardService;
use App\ResultCode;

class BankCardsController extends Controller
{
    public $validator;

    /**
     * 添加银行卡
     * Author:  zwg
     * Date:    180831
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     * @return 添加成功失败信息
     */
    public function addCard()
    {

        $data = ['bank_card_open_name' => request('bank_card_open_name'), 'bank_card_no' => request('bank_card_no'), 'bank_name' => request('bank_name')];
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
        BankCardService::changeDefault($data, request()->get('current_user'));
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

        BankCardService::delCard($data, request()->get('current_user'));
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
        foreach ($list as $value){
            //$value['logo'] = BankCards::getBankLogo($value['bank_name']);
            $value['logo'] = url("/images/bank.png");
        }

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