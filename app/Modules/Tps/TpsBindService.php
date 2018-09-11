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
use App\Exceptions\PasswordErrorException;
use App\Modules\Invite\InviteUserService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Merchant\MerchantService;
use App\Support\TpsApi;
use Illuminate\Support\Facades\DB;

class TpsBindService extends BaseService
{

    /**
     * 根据用户ID及类型获取用户绑定的tps帐号
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
     * 根据tps帐号获取该tps帐号绑定的信息
     * @param $tpsAccount
     * @return TpsBind
     */
    public static function getTpsBindInfoByTpsAccount($tpsAccount)
    {
        // 根据tps帐号获取该tps帐号绑定的信息
    	return TpsBind::where('tps_account', $tpsAccount)
    	    ->first();
    }

    /**
     * 运营中心绑定TPS帐号
     * @param $operId
     * @param $email
     * @return TpsBind
     * @throws \Exception
     */
    public static function bindTpsAccountForOper($operId, $email)
    {
        // 检测运营中心是否已绑定过TPS帐号
        $bindInfo = self::getTpsBindInfoByOriginInfo($operId, TpsBind::ORIGIN_TYPE_OPER);
        if(!empty($bindInfo)){
            throw new BaseResponseException('该运营中心已绑定TPS帐号, 不能重复绑定');
        }
        // 检测该TPS帐号是否已被其他运营中心绑定
        $bindInfo = self::getTpsBindInfoByTpsAccount($email);
        if(!empty($bindInfo)){
            throw new BaseResponseException('该tps帐号已被使用, 不能再次绑定');
        }

        DB::beginTransaction();

        try{
            // 调用TPS接口, 生成TPS帐号
            $tpsPassword = 'a12345678';
            $result = TpsApi::createTpsAccount($email, $tpsPassword);
            if($result['code'] !== 0 || empty($result['data']['uid'])){
                throw new BaseResponseException($result['msg']);
            }

            $record = new TpsBind();
            $record->origin_type = TpsBind::ORIGIN_TYPE_OPER;
            $record->origin_id = $operId;
            $record->tps_account = $email;
            $record->tps_uid = $result['data']['uid'];
            $record->save();

            // 生成完帐号, 发送邮件
            TpsApi::sendEmail($email, '创建成功通知', "您的帐号 $email 创建成功, 密码为 $tpsPassword, 请及时登录tps商城重置您的密码");
        } catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }

    	DB::commit();
    	return $record;
    }

    /**
     * 商户绑定TPS帐号
     * @param $merchantId
     * @param $mobile
     * @return TpsBind
     * @throws \Exception
     */
    public static function bindTpsAccountForMerchant($merchantId, $mobile)
    {
        // 查询商户是否已绑定
        $bindInfo = self::getTpsBindInfoByOriginInfo($merchantId, TpsBind::ORIGIN_TYPE_MERCHANT);
        if(!empty($bindInfo)){
            throw new BaseResponseException('该商户已绑定TPS帐号, 不能重复绑定');
        }
        // 查询帐号是否已被绑定
        $bindInfo = self::getTpsBindInfoByTpsAccount($mobile);
        if(!empty($bindInfo)){
            throw new BaseResponseException('该tps帐号已被使用, 不能再次绑定');
        }

        // 查询商户所属运营中心是否已绑定tps帐号
        $merchant = MerchantService::getById($merchantId, 'oper_id');
        if(empty($merchant)){
            throw new DataNotFoundException('商户信息不存在');
        }
        $operId = $merchant->oper_id;
        $operBindInfo = self::getTpsBindInfoByOriginInfo($operId, TpsBind::ORIGIN_TYPE_OPER);
        if(empty($operBindInfo)){
            throw new BaseResponseException('该商户所属运营中心尚未绑定TPS帐号, 请在运营中心绑定TPS后再进行绑定');
        }

        // 若符合条件, 则进行创建帐号
        DB::beginTransaction();


        try{
            // 调用TPS接口, 生成TPS帐号
            $tpsPassword = 'a12345678';
            $result = TpsApi::createTpsAccount($mobile, $tpsPassword, $operBindInfo->tps_account, 2);
            if($result['code'] !== 0 || empty($result['data']['uid'])) {
                throw new BaseResponseException($result['msg']);
            }

            $record = new TpsBind();
            $record->origin_type = TpsBind::ORIGIN_TYPE_MERCHANT;
            $record->origin_id = $merchantId;
            $record->tps_account = $mobile;
            $record->tps_uid = $result['data']['uid'];
            $record->save();

            // 生成完帐号, 发送短信通知, 目前没有自定义内容短信, 暂不发送
        } catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }

    	DB::commit();
    	return $record;
    }

    /**
     * 用户绑定TPS帐号逻辑
     * @param $userId
     * @param $tpsAccount
     * @param $tpsPassword
     * @return TpsBind
     * @throws \Exception
     */
    public static function bindTpsAccountForUser($userId, $tpsAccount, $tpsPassword)
    {
        // 调用TPS接口, 验证帐号密码是否正确
        $result = TpsApi::checkTpsAccount($tpsAccount, $tpsPassword);
        if($result['code'] !== 0 || empty($result['data']['uid']) ){
            if($result['code'] == 1301 || $result['code'] == 1302){
                $message = '很遗憾！绑定失败，请输入正确的TPS账号密码！';
                throw new PasswordErrorException($message);
            }else {
                $message = $result['msg'];
            }
            throw new BaseResponseException($message);
        }
        // 判断用户帐号是否已绑定
        $bindInfo = self::getTpsBindInfoByOriginInfo($userId, TpsBind::ORIGIN_TYPE_USER);
        if(!empty($bindInfo)){
            throw new BaseResponseException('该用户已绑定TPS帐号, 不能重复绑定');
        }
        // 判断tps帐号是否已绑定
        $bindInfo = self::getTpsBindInfoByTpsAccount($tpsAccount);
        if(!empty($bindInfo)){
            throw new BaseResponseException('很遗憾！绑定失败，该TPS帐号已被绑定！');
        }
        // 判断用户上级是否已绑定
        /*$inviteRecord = InviteUserService::getInviteRecordByUserId($userId);
        if(!empty($inviteRecord) && $inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_USER){
            $parentUserId = $inviteRecord->origin_id;
            $bindInfo = self::getTpsBindInfoByOriginInfo($parentUserId, TpsBind::ORIGIN_TYPE_USER);
            if(!empty($bindInfo)){
                throw new BaseResponseException('绑定失败，该帐号直属上级已绑定TPS会员，上下级不能同时绑定TPS');
            }
        }*/
        // 判断用户下级是否存在绑定过的帐号
        /*$inviteRecords = InviteUserService::getInviteRecordsByOriginInfo($userId, InviteUserRecord::ORIGIN_TYPE_USER);
        $subUserIds = $inviteRecords->pluck('user_id');
        if(count($subUserIds) > 0 &&
            !empty(
                $bindInfo = TpsBind::where('origin_type', TpsBind::ORIGIN_TYPE_USER)
                    ->whereIn('origin_id', $subUserIds)
                    ->first()
            )
        ){
            throw new BaseResponseException('绑定失败，该帐号直属下级已绑定TPS会员，上下级不能同时绑定TPS');
        }*/

        // 添加关联关系
        $record = new TpsBind();
        $record->origin_type = TpsBind::ORIGIN_TYPE_USER;
        $record->origin_id = $userId;
        $record->tps_account = $tpsAccount;
        $record->tps_uid = $result['data']['uid'];
        $record->save();

        return $record;
    }


}