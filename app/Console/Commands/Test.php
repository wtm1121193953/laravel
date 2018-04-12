<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

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
//        Redis::geoadd('merchant', 113.99531, 22.709883, 1);
//        Redis::geoadd('merchant', 114.002176,22.663003, 2);
//
//        dump(Redis::geodist('merchant', 1, 2, 'km'));
        dump(Redis::georadius('merchant', 114.002176,22.663103, 6, 'km', 'desc'));
        //
    }
}
