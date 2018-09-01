<?php

namespace App\Modules\Wallet;

use App\BaseService;
use App\Modules\Wallet\BankCard;
use Illuminate\Support\Facades\DB;
use App\Exceptions\BaseResponseException;
use App\ResultCode;
/**
 * Class BankCardService
 * @package App\Modules\Wallet
 * @param   array   $data
 * @param   \App\Modules\User\User $user
 * @param   integer $originType
 * Author:  Jerry
 * Date:    180830
 */
class BankCardService extends BaseService
{
    public static function addCard( $data, $user, $originType=BankCard::ORIGIN_TYPE_USER )
    {
        $bankCard = new BankCard;
        $bankCard->bank_card_open_name  = $data['bank_card_open_name'];
        $bankCard->bank_card_no         = $data['bank_card_no'];
        $bankCard->bank_name            = $data['bank_name'];
        $bankCard->origin_id            = $user->id;
        $bankCard->origin_type          = $originType;
        $bankCard->default              = BankCard::DEFAULT_UNSELECTED;
        if( !($bankCard->save()) ){
            throw new BaseResponseException(ResultCode::DB_INSERT_FAIL, '新增失败');
        }
    }

    /**
     * 修改默认卡
     * @param   array   $data
     * @param   \App\Modules\User\User $user
     * @param   integer $originType
     * @throws \Exception
     */
    public static function changeDefault( $data, $user, $originType=BankCard::ORIGIN_TYPE_USER )
    {
        $bankCard = new BankCard;
        // 开启事务
        DB::beginTransaction();
        try{
            // 全部改为未选
            $bankCard::where('origin_id', $user->id )
                ->where("origin_type", $originType)
                ->update(['default'=>BankCard::DEFAULT_UNSELECTED]);
            // 指定的为默认
            $bankCard::where('origin_id', $user->id )
                ->where("origin_type",$originType)
                ->where('id', $data['id'] )
                ->update(['default'=>BankCard::DEFAULT_SELECTED]);
            DB::commit();
        }catch ( \Exception $e ){
            DB::rollBack();
            throw new BaseResponseException( $e->getMessage(),ResultCode::DB_INSERT_FAIL);
        }
    }

    /**
     * @param   array    $data
     * @param   \App\Modules\User\User $user
     * @param   int $originType
     * @throws \Exception
     */
    public static function delCard( $data, $user, $originType=BankCard::ORIGIN_TYPE_USER )
    {
        $bankCard = new BankCard;
        $res = $bankCard::where('origin_id', $user->id )
            ->where("origin_type",$originType)
            ->where('id', $data['id'] )
            ->delete();
        if(!$res)
        {
            throw new BaseResponseException('删除失败',ResultCode::DB_INSERT_FAIL);
        }
    }

    /**
     * 通过ID获取银行卡
     * Author：  Jerry
     * Date：    180901
     * @param   int   $id
     * @param   \App\Modules\User\User|  $obj
     * @param   int $originType
     * @return \App\Modules\Wallet\BankCard
     */
    public static function getCardById( $id, $obj, $originType=BankCard::ORIGIN_TYPE_USER )
    {
        $bankCard = new BankCard;
        $card   = $bankCard::where('origin_id', $obj->id )
            ->where("origin_type",$originType)
            ->where('id', $id )
            ->first();
        return  $card;
    }

    public static function getList( $obj, $originType=BankCard::ORIGIN_TYPE_USER  )
    {
        $bankCard = new \App\Modules\Wallet\BankCard;
        $list = $bankCard::where('origin_id', $obj->id)
            ->where('origin_type', $originType)
            ->orderBy('default', 'desc')
            ->get();
        return $list;
    }
}
