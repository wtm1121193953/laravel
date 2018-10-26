<?php

namespace App\Console\Commands;

use App\Jobs\Schedule\SettlementDaily;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class TestPlatformSettlementDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:settlementDaily {date?}';

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
            $date = date('Y-m-d',strtotime('-1 day'));
        }
        SettlementDaily::dispatch(Carbon::createFromFormat('Y-m-d', $date));
    }
}
