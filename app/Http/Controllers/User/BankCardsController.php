<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Modules\Wallet\BankCardService;
use App\Http\Controllers\Controller;
use App\Result;
use App\Validator\Wallet\BankCard;
/**
 * Class BankCardsController
 * @package App\Http\Controllers\UserApp
 * Author:  Jerry
 * Date:    180830
 * 用户银行卡
 */
class BankCardsController extends Controller
{
    public $validator;
    public function __construct()
    {
        $this->validator = new BankCard;
    }

    /**
     * 添加银行卡
     * Author:  Jerry
     * Date:    180831
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    public function  addCard( Request $request)
    {
        $this->validator->scene('add')->check( $request->all() );
        BankCardService::addCard( $request );
        Result::success('添加银行卡成功');
    }

    /**
     * 设置默认银行卡
     * Author:  Jerry
     * Date:    180831
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function changDefault( Request $request )
    {
        $this->validator->scene('default')->check( $request->all() );
        BankCardService::changeDefault( $request );
        return Result::success('修改默认银行卡成功');
    }

    /**
     * 删除银行卡
     * Author:  Jerry
     * Date:    180831
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delCard( Request $request )
    {
        $this->validator->scene('default')->check( $request->all() );
        BankCardService::delCard( $request );
        return Result::success('删除银行卡成功');
    }

    /**
     * 获取银行卡列表
     * Author:  Jerry
     * Date:    180831
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getCardsList( Request $request )
    {
        $bankCard = new \App\Modules\Wallet\BankCard;
        $currentUser = $request->get('current_user');
        $list = $bankCard::where('origin_id', $currentUser->id)
                            ->where('origin_type', $currentUser->status)
                            ->orderBy('default', 'desc')
                            ->select();
        return Result::success('', $list);
    }
}
