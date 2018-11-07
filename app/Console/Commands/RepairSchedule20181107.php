<?php

namespace App\Console\Commands;

use App\Jobs\Schedule\PlatformTradeRecordsDailyJob;
use App\Jobs\Schedule\SettlementDaily;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RepairSchedule20181107 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:schedule20181107';

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
        SettlementDaily::dispatch(Carbon::createFromFormat('Y-m-d', '2018-11-07')->subDay());

        PlatformTradeRecordsDailyJob::dispatch(Carbon::createFromFormat('Y-m-d', '2018-11-07')->subDay()->endOfDay()->format('Y-m-d H:i:s'));
    }
}
