<?php

namespace App\Http\Controllers\User;

use App\ResultCode;
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
        $request->validate([
            'bank_card_no'          =>  'bail|required|numeric|unique:bank_cards',
            'bank_card_open_name'   =>  'required',
            'bank_name'             =>  'required',
            ]);
        BankCardService::addCard( $request->all(), $request->get('current_user') );
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
        BankCardService::changeDefault( $request->all(), $request->get('current_user') );
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
        BankCardService::delCard( $request->all(), $request->get('current_user') );
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
                            ->get();
        $res = BankCardService::getList( $request->get('current_user') );
        if( $res )
        {
            return Result::success( ['list'=>$list] );
        }else{
            return Result::error(ResultCode::DB_QUERY_FAIL,'无银行卡信息');
        }

    }
}
