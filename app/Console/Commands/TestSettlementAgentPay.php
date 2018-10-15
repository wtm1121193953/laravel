<?php

namespace App\Console\Commands;

use App\Jobs\SettlementAgentPay;
use Illuminate\Console\Command;

class TestSettlementAgentPay extends Command
{
    /**
     * example: php artisan test:settlementAgentPay 1,2,3
     *
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:settlementAgentPay {settlementIds}';

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
        $query = $this->argument('settlementIds');
        $settlementIds = explode(',',$query);
        SettlementAgentPay::dispatch($settlementIds);
    }
}
