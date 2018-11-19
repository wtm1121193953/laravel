<?php

namespace App\Jobs;

use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantAudit;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class InitMerchantFirstActiveTime implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $merchant;

    /**
     * Create a new job instance.
     *
     * @param Merchant $merchant
     */
    public function __construct(Merchant $merchant)
    {
        $this->merchant = $merchant;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $merchant = $this->merchant;
        $auditRecord = MerchantAudit::where('merchant_id', $merchant->id)
            ->where('status', MerchantAudit::STATUS_AUDIT_SUCCESS)
            ->orderBy('id')
            ->first();
        if (!empty($auditRecord)) {
            $merchant->first_active_time = $auditRecord->updated_at;
        } else {
            $merchant->first_active_time = $merchant->active_time;
        }
        $merchant->save();
    }
}
