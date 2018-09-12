<?php

namespace App\Console\Commands\Updates;

use App\Modules\Admin\AdminAuthRule;
use App\Modules\FeeSplitting\FeeSplittingRecord;
use App\Modules\FeeSplitting\FeeSplittingService;
use App\Modules\Merchant\Merchant;
use App\Modules\Merchant\MerchantService;
use App\Modules\Order\Order;
use App\Modules\Wallet\Wallet;
use App\Modules\Wallet\WalletBalanceUnfreezeRecord;
use App\Modules\Wallet\WalletBatch;
use App\Modules\Wallet\WalletBill;
use App\Modules\Wallet\WalletConsumeQuotaRecord;
use App\Modules\Wallet\WalletConsumeQuotaUnfreezeRecord;
use App\Modules\Wallet\WalletWithdraw;
use Illuminate\Console\Command;

class V1_4_3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:v1.4.3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        // 1. 清楚结算出的分润
        // 代码全部注释掉, 以免后面误操作
//        Wallet::where('id', '>', 0)->delete();
//        FeeSplittingRecord::where('id', '>', 0)->delete();
//        WalletBalanceUnfreezeRecord::where('id', '>', 0)->delete();
//        WalletBatch::where('id', '>', 0)->delete();
//        WalletBill::where('id', '>', 0)->delete();
//        WalletConsumeQuotaRecord::where('id', '>', 0)->delete();
//        WalletConsumeQuotaUnfreezeRecord::where('id', '>', 0)->delete();
//        WalletWithdraw::where('id', '>', 0)->delete();
//        Order::where('id', '>', 0)->update(['splitting_status' => 1, 'splitting_time' => null]);
//
//        // 删除错误的权限
//        AdminAuthRule::where('pid', 35)->delete();

    }
}
