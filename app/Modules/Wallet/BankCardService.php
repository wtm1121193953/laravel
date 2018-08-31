<?php

namespace App\Modules\Wallet;

use App\BaseService;
use App\Modules\Wallet\BankCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\BaseResponseException;
use App\ResultCode;
/**
 * Class BankCardService
 * @package App\Modules\Wallet
 * Author:  Jerry
 * Date:    180830
 */
class BankCardService extends BaseService
{
    public static function addCard( Request $request )
    {
        $bankCard = new BankCard;
        $bankCard->bank_card_open_name  = $request->input('bank_card_open_name');
        $bankCard->bank_card_no         = $request->input('bank_card_no');
        $bankCard->bank_name            = $request->input('bank_name');
        $bankCard->origin_id            = $request->get('current_user')->id;
//        $bankCard->origin_type          = BankCard::ORIGIN_TYPE_USER;
        $bankCard->origin_type               = $request->get('current_user')->status;
        $bankCard->default              = BankCard::DEFAULT_UNSELECTED;
        if( !($bankCard->save()) ){
            throw new BaseResponseException(ResultCode::DB_INSERT_FAIL, '新增失败');
        }
    }

    /**
     * 修改默认卡
     * @param Request $request
     * @throws \Exception
     */
    public static function changeDefault( Request $request )
    {
        $bankCard = new BankCard;
        $currentUser = $request->get('current_user');
        // 开启事务
        DB::beginTransaction();
        try{
            // 全部改为未选
            $bankCard::where('origin_id', $currentUser->id )
                ->where("origin_type",$currentUser->status)
                ->update(['default'=>BankCard::DEFAULT_UNSELECTED]);
            // 指定的为默认
            $bankCard::where('origin_id', $currentUser->id )
                ->where("origin_type",$currentUser->status)
                ->where('id', $request->input('id') )
                ->update(['default'=>BankCard::DEFAULT_SELECTED]);
            DB::commit();
        }catch ( \Exception $e ){
            DB::rollBack();
            throw new BaseResponseException( $e->getMessage(),ResultCode::DB_INSERT_FAIL);
        }
    }

    public static function delCard( Request $request)
    {
        $bankCard = new BankCard;
        $currentUser = $request->get('current_user');
        $res = $bankCard::where('origin_id', $currentUser->id )
            ->where("origin_type",$currentUser->status)
            ->where('id', $request->input('id') )
            ->delete();
        if(!$res)
        {
            throw new BaseResponseException(’删除失败‘,ResultCode::DB_INSERT_FAIL);
        }
    }
}
