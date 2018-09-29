<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/9/1
 * Time: 下午2:07
 */

namespace App\HTTP\Controllers\UserApp;

use App\Http\Controllers\Controller;
use App\Modules\Wallet\BankCard;
use App\Result;
use App\Support\BankCards;
use Illuminate\Http\Request;
use App\Modules\Wallet\BankCardService;

class BankCardsController extends Controller
{
    public $validator;

    /**
     * 添加银行卡
     * Author:  zwg
     * Date:    180831
     * @return \Illuminate\Http\JsonResponse
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
     * @throws \Exception
     */
    public function changDefault()
    {
        BankCardService::changeDefault(request()->get('id'), request()->get('current_user'));
        return Result::success('修改默认银行卡成功');
    }

    /**
     * 删除银行卡
     * Author:  zwg
     * Date:    180831
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function delCard()
    {
        BankCardService::delCard(request()->get('id'), request()->get('current_user'));
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
        $bankCard = new BankCard();
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
}