<?php

namespace App\Console\Commands;

use App\Jobs\Schedule\SettlementGenBatch;
use Illuminate\Console\Command;

class TestSettlementGenBatch extends Command
{
    /**
     * example: php artisan test:settlementAgentPay 1,2,3
     *
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:SettlementGenBatch';

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
        SettlementGenBatch::dispatch();
        dd('ok');

    }
}
