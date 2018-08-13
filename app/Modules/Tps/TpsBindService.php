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
     * @param $tpsAccount
     */
    public static function bindTpsAccountForMerchant($merchantId, $tpsAccount)
    {
        // TODO 商户绑定TPS账号
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