<?php

namespace App\Console\Commands;

use App\Jobs\Schedule\SettlementForPlatformWeekly;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class TestPlatformSettlementWeekly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:settlementWeekly {date?}';

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
        $date = $this->argument('date');
        if(empty($date)){
            $date = date('Y-m-d');
        }
        SettlementForPlatformWeekly::dispatch(Carbon::createFromFormat('Y-m-d', $date));
    }
}
