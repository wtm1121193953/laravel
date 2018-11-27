<?php

namespace App\Modules\Invite;

use App\BaseService;
use Illuminate\Support\Carbon;

/**
 * 邀请记录统计相关服务
 * Class InviteStatisticsService
 * @package App\Modules\Invite
 */
class InviteStatisticsService extends BaseService
{

    /**
     * 根据日期更新每日统计数据
     * @param $date
     */
    public static function batchUpdateDailyStatisticsByDate($date)
    {
        if($date instanceof Carbon){
            $date = $date->format('Y-m-d');
        }
        // 查询出每个origin对应当天的邀请数量并存储到每日统计表中
        InviteUserRecord::whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->groupBy('origin_id', 'origin_type')
            ->select('origin_id', 'origin_type')
            ->selectRaw('count(1) as total')
            ->orderBy('origin_id')
            ->chunk(1000, function ($records) use ($date) {
                foreach ($records as $record) {
                    // 统计时先查询, 如果存在, 则在原基础上修改
                    $statDaily = InviteStatisticsService::getDailyStatisticsByOriginInfoAndDate($record->origin_id, $record->origin_type, $date);
                    if(empty($statDaily)){
                        $statDaily = new InviteUserStatisticsDaily();
                        $statDaily->date = $date;
                        $statDaily->origin_id = $record->origin_id;
                        $statDaily->origin_type = $record->origin_type;
                    }
                    $statDaily->invite_count = $record->total;
                    $statDaily->save();
                }
            });
    }

    /**
     * 根据邀请人信息及日期, 该更新邀请人当日的邀请统计数据
     * @param $originId
     * @param $originType
     * @param $date
     * @return InviteUserStatisticsDaily
     */
    public static function updateDailyStatByOriginInfoAndDate($originId, $originType, $date)
    {
        if($date instanceof Carbon){
            $date = $date->format('Y-m-d');
        }
        $total = InviteUserRecord::where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->whereDate('created_at', $date)
            ->count('id');
        $statDaily = InviteStatisticsService::getDailyStatisticsByOriginInfoAndDate($originId, $originType, $date);
        if(empty($statDaily)){
            $statDaily = new InviteUserStatisticsDaily();
            $statDaily->date = $date;
            $statDaily->origin_id = $originId;
            $statDaily->origin_type = $originType;
        }
        $statDaily->invite_count = $total ?? 0;
        $statDaily->save();
        return $statDaily;
    }

    /**
     * 根据邀请人及日期信息获取已统计出的数据
     * @param $originId
     * @param $originType
     * @param $date
     * @return InviteUserStatisticsDaily
     */
    public static function getDailyStatisticsByOriginInfoAndDate($originId, $originType, $date)
    {
        if($date instanceof \Carbon\Carbon){
            $date = $date->format('Y-m-d');
        }
        $stat = InviteUserStatisticsDaily::where('date', $date)
            ->where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->first();
        return $stat;
    }

    /**
     * 根据日期获取邀请人当日的邀请数
     * @param $date
     * @param $originId
     * @param $originType
     * @return int
     */
    public static function getInviteCountByDate($originId, $originType, $date)
    {
        if($date instanceof \Carbon\Carbon){
            $date = $date->format('Y-m-d');
        }
        return InviteUserRecord::whereDate('created_at', $date)
            ->where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->count();
    }

    /**
     * 根据邀请人信息获取今天的邀请数量
     * @param $originId int 邀请人ID
     * @param $originType int 邀请人类型
     * @return int
     */
    public static function getTodayInviteCountByOriginInfo($originId, $originType)
    {
        return self::getInviteCountByDate($originId, $originType, Carbon::now());
    }

    /**
     * 获取用户类型邀请人当天的邀请数量
     * @param $userId
     * @return int
     */
    public static function getTodayInviteCountByUserId($userId)
    {
        return self::getTodayInviteCountByOriginInfo($userId, InviteChannel::ORIGIN_TYPE_USER);
    }

    /**
     * 获取用户类型邀请人当天的邀请数量
     * @param $merchantId
     * @return int
     */
    public static function getTodayInviteCountByMerchantId($merchantId)
    {
        return self::getTodayInviteCountByOriginInfo($merchantId, InviteChannel::ORIGIN_TYPE_MERCHANT);
    }

    /**
     * 获取超市商户邀请人当天的邀请数量
     * @param $cs_merchant_id
     * @return int
     */
    public static function getTodayInviteCountByCsMerchantId($cs_merchant_id)
    {
        return self::getTodayInviteCountByOriginInfo($cs_merchant_id, InviteChannel::ORIGIN_TYPE_CS_MERCHANT);
    }

    /**
     * 获取当日的邀请记录统计, 返回一个 InviteUserStatisticsDaily 对象, 不入库
     * @param $originId
     * @param $originType
     * @return InviteUserStatisticsDaily
     */
    public static function getTodayStatisticsByOriginInfo($originId, $originType)
    {
        $today = new InviteUserStatisticsDaily();
        $date = date('Y-m-d');
        $today->date = $date;
        $today->invite_count = self::getTodayInviteCountByOriginInfo($originId, $originType);

        return $today;
    }

    /**
     * 获取每日统计记录, 不包含当日数据
     * @param $originId
     * @param $originType
     * @param array $params 查询条件
     * @param int $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getDailyStatisticsListByOriginInfo($originId, $originType, $params = [], $pageSize = 15)
    {
        $data = InviteUserStatisticsDaily::where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->whereDate('date', '<', date('Y-m-d', time()))
            ->orderByDesc('date')
            ->paginate($pageSize);
        return $data;
    }

    /**
     * 获取商户的邀请总数
     * @param $originId
     * @param $originType
     * @return int
     */
    public static function getTotalInviteCountByOriginInfo($originId, $originType)
    {
        $totalCount = InviteUserStatisticsDaily::where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->where('date', '<', date('Y-m-d', time()))
            ->sum('invite_count');
        $todayInviteCount = self::getTodayInviteCountByOriginInfo($originId, $originType);
        return $totalCount + $todayInviteCount;
    }

    /**
     * 获取时间段内的邀请总数
     * @param $originId
     * @param $originType
     * @param $startDate
     * @param $endDate
     * @return int|mixed
     */
    public static function getTimeSlotInviteCountByOriginInfo($originId, $originType, $startDate, $endDate)
    {
        if ($startDate > $endDate) {
            return 0 ;
        }
        //如果结束日期包含今天，把今天的单独统计
        $today = date('Y-m-d', time());
        $todayInviteCount = 0;
        if ($endDate >= $today) {
            $todayInviteCount = self::getTodayInviteCountByOriginInfo($originId, $originType);
            $endDate = date('Y-m-d', time()-86400);
        }

        $totalCount = InviteUserStatisticsDaily::where('origin_id', $originId)
            ->where('origin_type', $originType)
            ->where('date', '>=',$startDate)
            ->where('date', '<=',$endDate)
            ->sum('invite_count');

        return $totalCount + $todayInviteCount;
    }

    /**
     * 获取用户类型邀请人的邀请总数
     * @param $userId
     * @return int
     */
    public static function getTotalInviteCountByUserId($userId)
    {
        return self::getTotalInviteCountByOriginInfo($userId, InviteChannel::ORIGIN_TYPE_USER);
    }

    /**
     * 获取商户类型邀请人的邀请总数
     * @param $merchantId
     * @return int
     */
    public static function getTotalInviteCountByMerchantId($merchantId)
    {
        return self::getTotalInviteCountByOriginInfo($merchantId, InviteChannel::ORIGIN_TYPE_MERCHANT);
    }

    /**
     * 获取超市商户类型邀请人的邀请总数
     * @param $cs_merchant_id
     * @return int
     */
    public static function getTotalInviteCountByCsMerchantId($cs_merchant_id)
    {
        return self::getTotalInviteCountByOriginInfo($cs_merchant_id, InviteChannel::ORIGIN_TYPE_CS_MERCHANT);
    }


}