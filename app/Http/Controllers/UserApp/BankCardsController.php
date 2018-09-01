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

class BankCardsController extends Controller{
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
    public function  addCard(Request $request)
    {
        $value = $this->getUserId();
        if (strlen($value) <= 0){
            return Result::error(ResultCode::UNLOGIN,'用户未登录');
        }
        $this->validator->scene('add')->check( $request->all() );
        BankCardService::addCard( $request );
        return Result::success('添加银行卡成功');
    }

    /**
     * 设置默认银行卡
     * Author:  zwg
     * Date:    180831
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function changDefault( Request $request )
    {
        $value = $this->getUserId();
        if (strlen($value) <= 0){
            return Result::error(ResultCode::UNLOGIN,'用户未登录');
        }
        $this->validator->scene('default')->check( $request->all() );
        BankCardService::changeDefault( $request );
        return Result::success('修改默认银行卡成功');
    }

    /**
     * 删除银行卡
     * Author:  zwg
     * Date:    180831
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delCard( Request $request )
    {
        $value = $this->getUserId();
        if (strlen($value) <= 0){
            return Result::error(ResultCode::UNLOGIN,'用户未登录');
        }
        $this->validator->scene('default')->check( $request->all() );
        BankCardService::delCard( $request );
        return Result::success('删除银行卡成功');
    }

    /**
     * 获取银行卡列表
     * Author:  zwg
     * Date:    180831
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getCardsList( Request $request )
    {
        $value = $this->getUserId();
        if (strlen($value) <= 0){
            return Result::error(ResultCode::UNLOGIN,'用户未登录');
        }
        $bankCard = new \App\Modules\Wallet\BankCard;
        $currentUser = $request->get('current_user');
        $list = $bankCard::where('origin_id', $currentUser->id)
            ->where('origin_type', $currentUser->status)
            ->orderBy('default', 'desc')
            ->get();
        return Result::success( $list );
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