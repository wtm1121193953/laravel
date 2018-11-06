<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RetryFailedJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retry_failed_jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重试失败任务队列';

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
        Log::info('开始手动执行失败队列');
        DB::table('failed_jobs')->orderBy('id')->chunk(100, function($jobs) {
            foreach ($jobs as $job) {
                $this->call('queue:retry',['id' => $job->id]);
                Log::info('失败队列ID', ['id' => $job->id]);
             }
        });
        Log::info('手动执行失败队列完成');
    }
}
