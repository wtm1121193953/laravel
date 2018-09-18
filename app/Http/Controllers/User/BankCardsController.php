<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Modules\Wallet\BankCardService;
use App\Http\Controllers\Controller;
use App\Result;
/**
 * Class BankCardsController
 * @package App\Http\Controllers\UserApp
 * Author:  Jerry
 * Date:    180830
 * 用户银行卡
 */
class BankCardsController extends Controller
{

    /**
     * 添加银行卡
     * Author:  Jerry
     * Date:    180831
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function  addCard( Request $request)
    {
        $max = '';
        for ($i=0;$i<35;$i++){
            $max .= '9';
        }

        $request->validate([
            'bank_card_no'          =>  'bail|required|min:10000000|max:'.$max.'|numeric',
            'bank_card_open_name'   =>  'required|max:20',
            'bank_name'             =>  'required',
            ]);
        $saveData = [
            'bank_card_no'          =>  $request->get('bank_card_no'),
            'bank_card_open_name'   =>  $request->get('bank_card_open_name'),
            'bank_name'             =>  $request->get('bank_name'),
        ];
        BankCardService::addCard( $saveData, $request->get('current_user') );
        return Result::success('添加银行卡成功');
    }


    /**
     * 设置默认银行卡
     * Author:  Jerry
     * Date:    180831
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function changDefault( Request $request )
    {
        $request->validate([
            'id'                    =>  'required|exists:bank_cards,id',
        ]);
        BankCardService::changeDefault( $request->get('id'), $request->get('current_user') );
        return Result::success('修改默认银行卡成功');
    }


    /**
     * 删除银行卡
     * Author:  Jerry
     * Date:    180831
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function delCard( Request $request )
    {
        $request->validate([
            'id'                    =>  'required|exists:bank_cards,id',
        ]);
        BankCardService::delCard( $request->get('id'), $request->get('current_user') );
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
        $res = BankCardService::getList( $request->get('current_user') );
        return Result::success( ['list'=>$res] );
    }
}
