<?php

namespace App\Console\Commands;

use App\Modules\UserCredit\UserCreditSettingService;
use Illuminate\Console\Command;

class ResetCreditSetting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:credit_setting';

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
     */
    public function handle()
    {
        //
        UserCreditSettingService::set('oper_profit_radio', 5);
        UserCreditSettingService::set('consume_quota_convert_ratio_to_parent', 50);
        UserCreditSettingService::set('credit_multiplier_of_amount', 100);
        UserCreditSettingService::set('user_level_1_of_credit_number', 0);
        UserCreditSettingService::set('user_level_2_of_credit_number', 1000);
        UserCreditSettingService::set('user_level_3_of_credit_number', 5000);
        UserCreditSettingService::set('user_level_4_of_credit_number', 10000);
        UserCreditSettingService::set('merchant_level_1_of_invite_user_number', 0);
        UserCreditSettingService::set('merchant_level_2_of_invite_user_number', 5000);
        UserCreditSettingService::set('merchant_level_3_of_invite_user_number', 10000);
        UserCreditSettingService::set('credit_to_self_ratio_of_user_level_1', 5);
        UserCreditSettingService::set('credit_to_self_ratio_of_user_level_2', 10);
        UserCreditSettingService::set('credit_to_self_ratio_of_user_level_3', 15);
        UserCreditSettingService::set('credit_to_self_ratio_of_user_level_4', 20);
        UserCreditSettingService::set('credit_to_parent_ratio_of_user_level_2', 10);
        UserCreditSettingService::set('credit_to_parent_ratio_of_user_level_3', 20);
        UserCreditSettingService::set('credit_to_parent_ratio_of_user_level_4', 30);
        UserCreditSettingService::set('credit_multiplier_of_merchant_level_1', 1);
        UserCreditSettingService::set('credit_multiplier_of_merchant_level_2', 1.5);
        UserCreditSettingService::set('credit_multiplier_of_merchant_level_3', 2);
    }
}
