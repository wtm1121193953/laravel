<?php

namespace App\Console\Commands;

use App\Modules\Settlement\SettlementPlatformKuaiQianBatchService;
use Illuminate\Console\Command;

class TestKuaiQian extends Command
{
    /**
     * example: php artisan test:settlementAgentPay 1,2,3
     *
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:kuaiqian {method}';

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
        $method = $this->argument('method');
        if ($method== 1) {
            $rt = SettlementPlatformKuaiQianBatchService::batchSend();

        } elseif ($method==2) {
            $rt = SettlementPlatformKuaiQianBatchService::batchQuery();
        } else {
            $rt = SettlementPlatformKuaiQianBatchService::$method();
        }
        dd($rt);

    }
}
