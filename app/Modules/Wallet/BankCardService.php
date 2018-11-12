<?php

namespace App\Modules\Wallet;

use App\BaseService;
use App\Modules\Bizer\Bizer;
use App\Modules\Bizer\BizerService;
use App\Modules\User\User;
use App\Modules\User\UserIdentityAuditRecordService;
use Illuminate\Database\Eloquent\Builder;
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
    public static function addCard($data, $obj, $originType = BankCard::ORIGIN_TYPE_USER )
    {
        // 检查开户名是否与实名验证信息一致
        self::checkIdentityAuditName($obj, $data['bank_card_open_name']);
        // 判断同一用户是否绑定相同的银行卡号
        $exist= BankCard::where('origin_id', $obj->id)
                        ->where('origin_type', $originType)
                        ->where('bank_card_no', $data['bank_card_no'])
                        ->exists();
        if( $exist )
        {
            throw new BaseResponseException('同一用户不可绑定相同银行卡', ResultCode::PARAMS_INVALID);
        }
        $bankCard = new BankCard;
        $bankCard->bank_card_open_name  = $data['bank_card_open_name'];
        $bankCard->bank_card_no         = $data['bank_card_no'];
        $bankCard->bank_name            = $data['bank_name'];
        $bankCard->origin_id            = $obj->id;
        $bankCard->origin_type          = $originType;
        $bankCard->bank_card_type       = BankCard::BANK_CARD_TYPE_PEOPLE;
        $bankCard->default              = BankCard::DEFAULT_UNSELECTED;
        if( !($bankCard->save()) ){
            throw new BaseResponseException('新增失败', ResultCode::DB_INSERT_FAIL);
        }

        //判断只有一张银行卡时设置为默认银行卡
        $query = BankCard::where('origin_id',$obj->id)
            ->where('origin_type',$originType);
        if($query ->count() == 1){
                $item = $query->first();
                $item->default = BankCard::DEFAULT_SELECTED;
                $item->save();
        }
    }

    /**
     * 检查开户名是否与实名验证信息一致
     * @param $obj
     * @param $checkName
     */
    public static function checkIdentityAuditName($obj, $checkName )
    {
        if ($obj instanceof User) {
            $record = UserIdentityAuditRecordService::getRecordByUserId($obj->id);
        } elseif ($obj instanceof Bizer) {
            $record = BizerService::getBizerIdentityAuditRecordByBizerId($obj->id);
        } else {
            throw new BaseResponseException('该对象不存在');
        }
        if( $record['name']!=$checkName ){
            throw new BaseResponseException('持卡人姓名必须和当前认证用户一致',ResultCode::PARAMS_INVALID );
        }
    }

    /**
     * 修改默认卡
     * @param   int   $cardId
     * @param   \App\Modules\User\User $user
     * @param   integer $originType
     * @throws \Exception
     */
    public static function changeDefault( $cardId, $user, $originType=BankCard::ORIGIN_TYPE_USER )
    {
        $query  = BankCard::where('origin_id', $user->id )
                        ->where("origin_type", $originType);
        // 开启事务
        DB::beginTransaction();
        try{
            // 全部改为未选
            $query->update(['default'=>BankCard::DEFAULT_UNSELECTED]);
            // 指定的为默认
            $query->where('id', $cardId )
                ->update(['default'=>BankCard::DEFAULT_SELECTED]);
            DB::commit();
        }catch ( \Exception $e ){
            DB::rollBack();
            throw new BaseResponseException( $e->getMessage(),ResultCode::DB_INSERT_FAIL);
        }
    }

    /**
     * @param   int    $cardId
     * @param   \App\Modules\User\User $user
     * @param   int $originType
     * @throws \Exception
     */
    public static function delCard( $cardId, $user, $originType=BankCard::ORIGIN_TYPE_USER )
    {
        $res = BankCard::where('origin_id', $user->id)
            ->where("origin_type",$originType)
            ->where('id', $cardId)
            ->delete();
        if(!$res)
        {
            throw new BaseResponseException('删除失败',ResultCode::DB_INSERT_FAIL);
        }

        //判断有无默认卡，无设置第一张为默认卡

        $isDefault = BankCard::where('origin_id',$user->id)->where('origin_type',$originType)->where('default',BankCard::DEFAULT_SELECTED)->count();
        if($isDefault == 0){
            $item = BankCard::where('origin_id',$user->id)->where('origin_type',$originType)->first();
            if ($item){
                $item->default = BankCard::DEFAULT_SELECTED;
                $item->save();
            }
        }
    }

    /**
     * 通过ID获取银行卡
     * Author：  Jerry
     * Date：    180901
     * @param   int   $id
     * @return \App\Modules\Wallet\BankCard
     */
    public static function getCardById($id)
    {
        return BankCard::find($id);
    }

    public static function getList( $obj, $originType=BankCard::ORIGIN_TYPE_USER  )
    {
        return BankCard::where('origin_id', $obj->id)
            ->where('origin_type', $originType)
            ->orderBy('default', 'desc')
            ->get();
    }

    /**
     * 通过银行卡号获取银行卡
     * Author: zwg
     * Date: 180903
     * @param String $bankCardNo
     * @param   int $originType
     * @return \App\Modules\Wallet\BankCard
     */
    public static function getCardByBankCardNo($bankCardNo, $originType=BankCard::ORIGIN_TYPE_USER ){
        return BankCard::where('bank_card_no', $bankCardNo)
            ->where("origin_type",$originType)
            ->first();
    }


    /**
     * 获取银行列表
     * @param bool $onlyStatusUsable
     * @return Bank[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getBankList($onlyStatusUsable = false)
    {
        return  Bank::when($onlyStatusUsable, function (Builder $query) {
            $query->where('status', Bank::STATUS_USABLE);
        })->get();
    }

    /**
     * 通过用户id和类型获取一张银行卡信息
     * @param $originId
     * @param $originType
     * @return BankCard
     */
    public static function getBankCardByOriginInfo($originId, $originType)
    {
        $bankCard = BankCard::where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->first();
        return $bankCard;
    }

    /**
     * 修改银行卡
     * @param $originId
     * @param $originType
     * @param $params
     * @return BankCard
     */
    public static function editBankCard($originId, $originType, $params)
    {
        $bankCardNo = array_get($params, 'bankCardNo');
        $bankName = array_get($params, 'bankName');

        $bankCard = self::getBankCardByOriginInfo($originId, $originType);
        $bankCard->bank_card_no = $bankCardNo;
        $bankCard->bank_name = $bankName;
        $bankCard->save();

        return $bankCard;
    }
}
