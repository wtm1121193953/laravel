<?php

namespace App\Jobs;

use App\Modules\Invite\InviteUserRecord;
use App\Modules\Merchant\MerchantService;
use App\Modules\Merchant\MerchantStatisticsService;
use App\Modules\Oper\OperStatisticsService;
use App\Modules\User\UserStatisticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class UpdateMarketingStatisticsInviteInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $originId;
    protected $originType;
    protected $date;

    /**
     * 更新营销统计的邀请相关数据
     * Create a new job instance.
     *
     * @param $originId
     * @param $originType
     * @param $date
     */
    public function __construct($originId, $originType, $date)
    {
        $this->originId = $originId;
        $this->originType = $originType;
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->originType == InviteUserRecord::ORIGIN_TYPE_USER) {
            UserStatisticsService::updateStatisticsInviteInfo($this->originId, $this->date);
        } elseif ($this->originType == InviteUserRecord::ORIGIN_TYPE_MERCHANT) {
            MerchantStatisticsService::updateStatisticsInviteInfo($this->originId, $this->date);

            $merchant = MerchantService::getById($this->originId);
            if (empty($merchant)) {
                Log::error('updateMarketingStatisticsInviteInfo更新营销统计商户邀请人数, 商户为空', [
                    'origin_id' => $this->originId,
                    'origin_type' => $this->originType,
                    'date' => $this->date,
                ]);
                return;
            }

            OperStatisticsService::updateStatisticsInviteInfo($merchant->oper_id, $this->date);
        } elseif ($this->originId == InviteUserRecord::ORIGIN_TYPE_OPER) {
            OperStatisticsService::updateStatisticsInviteInfo($this->originId, $this->date);
        } else {
            Log::info('更新营销统计的邀请相关数据Job, 参数类型错误', [
                'origin_id' => $this->originId,
                'origin_type' => $this->originType,
                'date' => $this->date,
            ]);
            return;
        }
    }
}
