<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14
 * Time: 19:20
 */

namespace App\Modules\User;

use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\ParamInvalidException;
use App\Modules\Admin\AdminUser;
use App\Modules\Country\CountryService;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\Invite\InviteUserService;
use App\Modules\Invite\InviteUserUnbindRecord;
use App\Modules\Setting\SettingService;
use App\Modules\UserCredit\UserConsumeQuotaRecord;
use App\Modules\UserCredit\UserCredit;
use App\Modules\UserCredit\UserCreditRecord;
use App\Modules\UserCredit\UserCreditSettingService;
use App\Result;
use App\Support\RedisLock;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Invite\InviteChannelService;
use App\Modules\Merchant\Merchant;
use App\Modules\Oper\Oper;
use App\Modules\Sms\SmsVerifyCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService extends BaseService
{


    /**
     * @param $mobile
     * @param $verifyCode
     * @return array
     * @throws \Exception
     */
    public static function userAppLogin($mobile, $verifyCode)
    {
        // 获取锁
        $lock_key = 'user_register:' . $mobile;
        $is_lock = RedisLock::lock($lock_key, 5);

        if($is_lock){
            DB::beginTransaction();
            try {
                // 正式环境开放一个手机号,用于ios审核
                $needSkipVerifyCode = !App::environment('production') || $mobile == '18038019967';
                // 非正式环境时, 验证码为6666为通过验证
                if(! ($needSkipVerifyCode && $verifyCode == '6666') ){
                    $verifyCodeRecord = SmsVerifyCode::where('mobile', $mobile)
                        ->where('verify_code', $verifyCode)
                        ->where('status', 1)
                        ->where('expire_time', '>', Carbon::now())
                        ->first();
                    if(empty($verifyCodeRecord)){
                        throw new ParamInvalidException('验证码错误');
                    }
                    $verifyCodeRecord->status = 2;
                    $verifyCodeRecord->save();
                }

                // 验证通过, 查询当前用户是否存在, 不存在则创建用户
                if(! $user = User::where('mobile', $mobile)->first()){
                    $user = new User();
                    $user->mobile = $mobile;
                    $user->save();
                    // 重新查一次用户信息, 补充用户信息中的全部字段
                    $user = User::find($user->id);
                }

                // 如果存在邀请渠道ID, 查询用户是否已被邀请过
                $inviteChannelId = request('inviteChannelId');
                if($inviteChannelId){
                    $inviteChannel = InviteChannelService::getById($inviteChannelId);
                    if(empty($inviteChannel)){
                        throw new ParamInvalidException('邀请渠道不存在');
                    }
                    InviteUserService::bindInviter($user->id, $inviteChannel);
                }
                // 如果用户存在旧token，则清除，限制用户只能登陆1终端
                if( Cache::has('user_id_to_token_' . $user->id)){
                    $oldToken = Cache::get('user_id_to_token_' . $user->id);
                    Cache::forget('user_id_to_token_' . $user->id);
                    Cache::forget('token_to_user_' . $oldToken);
                }
                // 生成token并返回
                $token = str_random(64);
                Cache::put('token_to_user_' . $token, $user, 60 * 24 * 30);
                Cache::put('user_id_to_token_' . $user->id, $token, 60 * 24 * 30);

                DB::commit();
                RedisLock::unlock($lock_key);
            }catch (\Exception $e){
                DB::rollBack();
                RedisLock::unlock($lock_key);
                throw $e;
            }
        } else {
            throw new BaseResponseException('重复请求');
        }


        $userMapping = UserMapping::where('user_id', $user->id)->first();
        if (!empty($userMapping)){
            if ($userMapping->origin_type == 1){
                $merchant = Merchant::findOrFail($userMapping->origin_id);
                $user->mapping_merchant_name = $merchant->name;
                $user->merchant_level = $merchant->level;
                $user->merchant_level_text = Merchant::getLevelText($merchant->level);
            }else{
                $oper = Oper::findOrFail($userMapping->origin_id);
                $user->mapping_oper_name = $oper->name;
            }
        }
        $user->level_text = User::getLevelText($user->level);

        $record = UserIdentityAuditRecordService::getRecordByUserId($user->id);
        if ($record) {
            $user->identityInfoStatus = $record->status;
        } else {
            $user->identityInfoStatus = 4;
        }

        return [
            'user' => $user,
            'token' => $token
        ];
    }
    /**
     * 获取会员列表
     * @param $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getList($params){

        $mobile = array_get($params, 'mobile');

        $users  = User::select('id','name','mobile','email','created_at')
            ->when($mobile, function (Builder $query) use ($mobile){
                $query->where('mobile','like','%'.$mobile.'%');
            })
            ->orderByDesc('created_at')
            ->paginate();

        $users->each(function ($item){

            $parentName = InviteUserService::getParentName($item->id);
            if($parentName){
                $item->isBind = 1;
                $item->parent = $parentName;
            }else {
                $item->parent = '未绑定';
                $item->isBind = 0;
            }
        });

        return $users;
    }

    /**
     * 获取会员列表
     * @param $params
     * @param bool $return_query
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function userList($params,bool $return_query = false){

        if($params['status']){
            if(!is_array($params['status'])){
                $statusArr = explode(',',$params['status']);
            }else{
                $statusArr = $params['status'];
            }
        }else{
            $statusArr = [];
        }


        if($params['identityStatus']){
            if(!is_array($params['identityStatus'])){
                $identityStatusArr = explode(',',$params['identityStatus']);
            }else{
                $identityStatusArr = $params['identityStatus'];
            }
        }else{
            $identityStatusArr = [];
        }


        $query  = User::select('id','name','mobile','email','created_at','status')
            ->when($params['mobile'], function (Builder $query) use ($params){
                $query->where('mobile','like','%'.$params['mobile'].'%');
            })
            ->when($params['name'], function (Builder $query) use ($params){
                $query->where('name','like','%'.$params['name'].'%');
            })
            ->when($params['id'], function (Builder $query) use ($params){
                $query->where('id','=',$params['id']);
            })
            ->when($params['startDate'] && $params['endDate'], function (Builder $query) use ($params){
                $query->where('created_at', '>=', $params['startDate'] . ' 00:00:00');
                $query->where('created_at', '<=', $params['endDate']. ' 23:59:59');
            })
            ->when($statusArr, function (Builder $query) use ($statusArr){
                $query->whereIn('status', $statusArr);
            })
            ->when($identityStatusArr, function (Builder $query) use ($identityStatusArr) {
                $query->whereHas('identityAuditRecord', function (Builder $query) use ($identityStatusArr) {
                    $query->whereIn('status', $identityStatusArr);
                });
            })
            ->with('identityAuditRecord:user_id,status')
            ->orderByDesc('created_at');

        if ($return_query) {
            return $query;
        }

        $users = $query->paginate();

        $users->each(function ($item){
            $item->stauts_val = User::getStatusText($item['status']);
            if (!empty($item->identityAuditRecord->status)) {
                $item->identity_status_text = UserIdentityAuditRecord::getStatusText($item->identityAuditRecord->status);
            } else {
                $item->identity_status_text = '未提交';
            }

            $parentName = InviteUserService::getParentName($item->id);
            if($parentName){
                $item->isBind = 1;
                $item->parent = $parentName;
            }else {
                $item->parent = '未绑定';
                $item->isBind = 0;
            }
        });

        return $users;
    }

    /**
     * 获取会员审核列表
     * @param $params
     * @param bool $return_query
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function identity($params,bool $return_query = false){


        if($params['status']){
            if(!is_array($params['status'])){
                $statusArr = explode(',',$params['status']);
            }else{
                $statusArr = $params['status'];
            }
        }else{
            $statusArr = [];
        }
        $query  = UserIdentityAuditRecord::select('*')
            ->when($params['mobile'], function (Builder $query) use ($params){
                $query->whereHas('user',function($q) use ($params) {
                    $q->where('mobile', 'like', "%{$params['mobile']}%");
                });
            })
            ->when($params['name'], function (Builder $query) use ($params){
                $query->where('name','like','%'.$params['name'].'%');
            })
            ->when($params['id_card_no'], function (Builder $query) use ($params){
                $query->where('id_card_no','like','%'.$params['id_card_no'].'%');
            })
            ->when($params['id'], function (Builder $query) use ($params){
                $query->where('user_id', '=', $params['id']);
            })
            ->when($params['startDate'] && $params['endDate'], function (Builder $query) use ($params){
                $query->where('created_at', '>=', $params['startDate'] . ' 00:00:00');
                $query->where('created_at', '<=', $params['endDate']. ' 23:59:59');
            })
            ->when($params['status'], function (Builder $query) use ($statusArr){
                $query->whereIn('status', $statusArr);
            })
            ->with('user')
            ->orderByDesc('created_at');

        if ($return_query) {
            return $query;
        }

        $users = $query->paginate();

        $users->each(function ($item){

            $item->countryName = CountryService::getNameZhById($item->country_id);
            $item->status_val = UserIdentityAuditRecord::getStatusText($item->status);
            if (!empty($item->user)) {
                $item->user->status_val = User::getStatusText($item->user->status);
            }

        });

        return $users;
    }

    /**
     * 用户认证信息详情
     * @param $id
     */
    public static function identityDetail($id)
    {
        return UserIdentityAuditRecord::with('user')->findOrFail($id);
    }

    /**
     * 审核
     * @param $id
     * @param $status
     * @param string $reason
     * @return bool
     */
    public static function identityDo($id,$status,$reason='')
    {

        if (empty($reason)) {
            $reason = '';
        }
        $info = UserIdentityAuditRecord::findOrFail($id);

        $info->status = $status;
        $info->reason = $reason;

        /** @var AdminUser $user */
        $user = session(config('admin.user_session'));
        if (!empty($user->id)) {
            $info->update_user = $user->id;
        }
        $rs = $info->save();
        return $rs;
    }

    /**
     * 通过电话号码查询用户详情
     * @param $mobile
     * @return User
     */
    public static function getUserByMobile($mobile)
    {
        $user = User::where('mobile', $mobile)->first();
        return $user;
    }

    /**
     * 通过id获取用户信息
     * @param $userId
     * @return User
     */
    public static function getUserById($userId)
    {
        $user = User::find($userId);

        return $user;
    }

    /**
     * 根据openId获取用户信息
     * @param $openId
     * @return User|null
     */
    public static function getUserByOpenId($openId)
    {
        $userId = UserOpenIdMapping::where('open_id', $openId)->value('user_id');
        if($userId){
            $user = UserService::getUserById($userId);
            return $user;
        }
        return null;
    }

    /**
     * 获取积分转换率 (百分比)
     * @param $merchantId
     * @param $userId
     * @param $userLevel
     * @return float
     */
    public static function getPayAmountToCreditRatio($merchantId,$userId,$userLevel)
    {

        $settlementRate = Merchant::where('id', $merchantId)->value('settlement_rate'); //分利比例
        if (!isset($user->level) || empty($userLevel)){
            $userLevel = User::where('id', $userId)->value('level'); //用户等级
        }
        $creditRatio = UserCreditSettingService::getCreditToSelfRatioSetting($userLevel); //自反比例
        $creditMultiplierOfAmount = SettingService::getValueByKey('credit_multiplier_of_amount'); //积分系数
        $creditRatio = $settlementRate * $creditRatio * $creditMultiplierOfAmount / 100.0 ; //积分换算比例

        return $creditRatio;
    }

    /**
     * 用户积分列表
     * @param $userId
     * @param $year
     * @param $month
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public static function getUserCreditList($userId,$year,$month)
    {
        $data = UserCreditRecord::where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->paginate();

        // 获取当月总积分以及总消耗积分
        $totalInCredit = UserCreditRecord::where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('inout_type', 1)
            ->sum('credit');
        $totalOutCredit = UserCreditRecord::where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('inout_type', 2)
            ->sum('credit');

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'totalInCredit' => $totalInCredit,
            'totalOutCredit' => $totalOutCredit,
        ]);
    }

    /**
     * 通过user_id获取我的累计积分和累计消费额
     * @param $userId
     * @return UserCredit
     */
    public static function getUserIdCredit($userId)
    {
        return UserCredit::where('user_id', $userId)->first();
    }

    public static function bindInfoForUserApp($userId)
    {
        $inviteRecord = InviteUserRecord::where('user_id', $userId)->first();
        if(empty($inviteRecord)){
            return null;
        }
        $mappingUser = null; // 上级商户或运营中心关联的用户
        $merchant = null; // 关联的上级商户
        $oper = null; // 关联的上级运营中心
        $user = null; // 关联的上级用户
        if($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_MERCHANT){
            $merchant = Merchant::where('id', $inviteRecord->origin_id)->first();
            $userMapping = UserMapping::where('origin_id', $inviteRecord->origin_id)
                ->where('origin_type', UserMapping::ORIGIN_TYPE_MERCHANT)
                ->first();
            if(!empty($userMapping)){
                $mappingUser = User::find($userMapping->user_id);
            }
        }else if($inviteRecord->origin_type == InviteUserRecord::ORIGIN_TYPE_OPER){
            $oper = Oper::where('id', $inviteRecord->origin_id)->first();
            $userMapping = UserMapping::where('origin_id', $inviteRecord->origin_id)
                ->where('origin_type', UserMapping::ORIGIN_TYPE_OPER)
                ->first();
            if(!empty($userMapping)){
                $mappingUser = User::find($userMapping->user_id);
            }
        }else {
            $user = User::find($inviteRecord->origin_id);
        }
        return [
            'origin_type' => $inviteRecord->origin_type,
            'user' => $user,
            'merchant' => $merchant,
            'oper' => $oper,
            'mappingUser' => $mappingUser,
        ];
    }

    public static function getUserConsumeQuotaRecordList($userId,$year,$month)
    {
        $data = UserConsumeQuotaRecord::where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->paginate();

        // 获取当月总消费额
        $totalInConsumeQuota = UserConsumeQuotaRecord::where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('inout_type', 1)
            ->sum('consume_quota');
        $totalOutConsumeQuota = UserConsumeQuotaRecord::where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('inout_type', 2)
            ->sum('consume_quota');

        return Result::success([
            'list' => $data->items(),
            'total' => $data->total(),
            'totalInConsumeQuota' => $totalInConsumeQuota,
            'totalOutConsumeQuota' => $totalOutConsumeQuota,
        ]);
    }

    public static function unbindForUserApp($userId)
    {
        //獲取解綁記錄
        $UnbindInviteRecordid = InviteUserUnbindRecord:: where([
            ['user_id', '=', $userId],
            ['status', '=', '2'],
        ])->first();
        if ($UnbindInviteRecordid) {
            throw new BaseResponseException('每个账户只有一次解绑机会，您已没有解绑次数');
        } else {
            $inviteRecord = InviteUserRecord::where('user_id', $userId)->first();
            if (empty($inviteRecord)) {
                throw new BaseResponseException('未绑定邀请人');
            } else {
                $inviteRecord->delete();
                $unbindInviteRecord = new InviteUserUnbindRecord();
                $unbindInviteRecord->user_id = $userId;
                $unbindInviteRecord->status = 2;
                $unbindInviteRecord->save();
                return true;
            }
        }
    }

    public static function getinfoForUserApp()
    {
        $user = request()->get('current_user');
        $userMapping = UserMapping::where('user_id', $user->id)->first();
        if (!empty($userMapping)){
            if ($userMapping->origin_type == 1){
                $merchant = Merchant::findOrFail($userMapping->origin_id);
                $user->mapping_merchant_name = $merchant->name;
                $user->merchant_level = $merchant->level;
                $user->merchant_level_text = Merchant::getLevelText($merchant->level);
            }else{
                $oper = Oper::findOrFail($userMapping->origin_id);
                $user->mapping_oper_name = $oper->name;
            }
        }

        $user->level_text = User::getLevelText($user->level);

        return $user;
    }

    /**
     * 通过手机号获取user中某个字段的数组
     * @param $mobile
     * @param $field
     * @return \Illuminate\Support\Collection
     */
    public static function getUserColumnArrayByMobile($mobile, $field)
    {
        $arr = User::where('mobile', 'like', "%$mobile%")
            ->get()
            ->pluck($field);
        return $arr;
    }

    /**
     * 获取用户头像
     * @author zwg
     * @param $userId
     * @return string 用户头像地址
     */
    public static function getUserAvatarUrlByUserId($userId){
        return User::where('id',$userId)->value('avatar_url');
    }

    /**
     * 获取所有用户
     * @return User[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAll()
    {
        $users = User::all();
        return $users;
    }
}