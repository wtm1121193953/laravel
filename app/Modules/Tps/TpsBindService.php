<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/7/23
 * Time: 16:35
 */

namespace App\Modules\Tps;

use App\BaseService;

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
        // todo 根据tps账号获取该tps账号绑定的信息
    	return TpsBind::where('tps_account', $tpsAccount)
    	->first(); 
    }

    /**
     * 运营中心绑定TPS账号
     * @param $operId
     * @param $tpsAccount
     */
    public static function bindTpsAccountForOper($operId, $tpsAccount)
    {
        // todo 运营中心绑定账号逻辑
    	$record = new TpsBind();
    	$record->origin_type = 3;
    	$record->origin_id = $operId;
    	$record->tps_account = $tpsAccount;
    	$record->save();
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