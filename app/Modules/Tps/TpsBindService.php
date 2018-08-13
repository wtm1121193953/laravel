<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/23
 * Time: 16:35
 */

namespace App\Modules\Tps;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Modules\Merchant\MerchantService;
use App\Modules\Sms\SmsService;
use App\Support\MicroServiceApi;
use App\Support\TpsApi;
use Illuminate\Support\Facades\DB;

class TpsBindService extends BaseService
{

    /**
     * 根据用户ID及类型获取用户绑定的tps账号
     * @param $originId
     * @param $originType
     * @return TpsBind
     */
    public static function getTpsBindInfoByOriginInfo($originId, $originType)
    {
        return TpsBind::where('origin_id', $originId)
            ->where('origin_type', $originType)->first();
    }

    /**
     * 根据tps账号获取该tps账号绑定的信息
     * @param $tpsAccount
     * @return TpsBind
     */
    public static function getTpsBindInfoByTpsAccount($tpsAccount)
    {
        // 根据tps账号获取该tps账号绑定的信息
    	return TpsBind::where('tps_account', $tpsAccount)
    	    ->first();
    }

    /**
     * 运营中心绑定TPS账号
     * @param $operId
     * @param $email
     * @return TpsBind
     * @throws \Exception
     */
    public static function bindTpsAccountForOper($operId, $email)
    {
        // 检测运营中心是否已绑定过TPS账号
        $bindInfo = self::getTpsBindInfoByOriginInfo($operId, TpsBind::ORIGIN_TYPE_OPER);
        if(!empty($bindInfo)){
            throw new BaseResponseException('该运营中心已绑定TPS账号, 不能重复绑定');
        }
        // 检测该TPS账号是否已被其他运营中心绑定
        $bindInfo = self::getTpsBindInfoByTpsAccount($email);
        if(!empty($bindInfo)){
            throw new BaseResponseException('该tps账号已被使用, 不能再次绑定');
        }

        DB::beginTransaction();
    	$record = new TpsBind();
    	$record->origin_type = TpsBind::ORIGIN_TYPE_OPER;
    	$record->origin_id = $operId;
    	$record->tps_account = $email;
    	$record->save();

        try{
            // 调用TPS接口, 生成TPS账号
            $tpsPassword = 'a12345678';
            $result = TpsApi::createTpsAccount($email, $tpsPassword);
            if($result['code'] !== 0){
                throw new BaseResponseException($result['msg']);
            }
            // 生成完账号, 发送邮件
            TpsApi::sendEmail($email, '创建成功通知', "您的账号 $email 创建成功, 密码为 $tpsPassword, 请及时登录tps商城重置您的密码");
        } catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }

    	DB::commit();
    	return $record;
    }

    /**
     * 商户绑定TPS账号
     * @param $merchantId
     * @param $mobile
     * @throws \Exception
     */
    public static function bindTpsAccountForMerchant($merchantId, $mobile)
    {
        // TODO 商户绑定TPS账号
        // 查询商户是否已绑定
        $bindInfo = self::getTpsBindInfoByOriginInfo($merchantId, TpsBind::ORIGIN_TYPE_MERCHANT);
        if(!empty($bindInfo)){
            throw new BaseResponseException('该商户已绑定TPS账号, 不能重复绑定');
        }
        // 查询账号是否已被绑定
        $bindInfo = self::getTpsBindInfoByTpsAccount($mobile);
        if(!empty($bindInfo)){
            throw new BaseResponseException('该tps账号已被使用, 不能再次绑定');
        }

        // 查询商户所属运营中心是否已绑定tps账号
        $merchant = MerchantService::getById($merchantId, 'oper_id');
        if(empty($merchant)){
            throw new DataNotFoundException('商户信息不存在');
        }
        $operId = $merchant->oper_id;
        $operBindInfo = self::getTpsBindInfoByOriginInfo($operId, TpsBind::ORIGIN_TYPE_OPER);
        if(empty($operBindInfo)){
            throw new BaseResponseException('该商户所属运营中心尚未绑定TPS账号, 请在运营中心绑定TPS后再进行绑定');
        }

        // 若符合条件, 则进行创建账号
        DB::beginTransaction();
    	$record = new TpsBind();
    	$record->origin_type = TpsBind::ORIGIN_TYPE_OPER;
    	$record->origin_id = $operId;
    	$record->tps_account = $mobile;
    	$record->save();

        try{
            // 调用TPS接口, 生成TPS账号
            $tpsPassword = 'a12345678';
            $result = TpsApi::createTpsAccount($mobile, $tpsPassword, $operBindInfo->tps_account, 2);
            if($result['code'] !== 0){
                throw new BaseResponseException($result['msg']);
            }
            // 生成完账号, 发送短信通知, 目前没有自定义内容短信, 暂不发送
        } catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }

    	DB::commit();
    	return $record;
    }

    /**
     * 用户绑定TPS账号逻辑
     * @param $userId
     * @param $tpsAccount
     */
    public static function bindTpsAccountForUser($userId, $tpsAccount)
    {
        // todo 用户绑定TPS账号逻辑
    }

}