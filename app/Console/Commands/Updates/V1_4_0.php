<?php

namespace App\Console\Commands\Updates;

use App\Jobs\InviteStatisticsDailyUpdateByDate;
use App\Jobs\UserOrderCountUpdateJob;
use App\Modules\Invite\InviteUserRecord;
use App\Modules\User\User;
use App\Modules\Wechat\MiniprogramScene;
use Carbon\Carbon;
use Illuminate\Console\Command;

class V1_4_0 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:v1.4.0';

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
        // 更新用户下单总次数
        $this->info('更新用户下单总次数 Start');
        $bar = $this->output->createProgressBar(User::count('id'));
        User::select('id')->chunk(1000, function($list) use ($bar){
            $list->map(function(User $user) use ($bar){
                UserOrderCountUpdateJob::dispatch($user->id);
                $bar->advance();
            });
            unset($list);
        });
        $bar->finish();
        $this->info("\n更新用户下单总次数 Finished");

        // 清除小程序场景表中的小程序码链接, 以便重新生成小程序码
        $this->info('清除小程序场景表中的小程序码链接 Start');
        MiniprogramScene::where('id', '>', 0)
            ->update(['qrcode_url' => '']);
        $this->info('清除小程序场景表中的小程序码链接 Finished');

        // 更新邀请记录统计数据
        $this->info("更新邀请记录统计数据 Start");
        /** @var Carbon $date */
        $date = InviteUserRecord::orderBy('created_at')->value('created_at');
        $totalDays = $date->diffInDays(Carbon::today());
        $bar = $this->output->createProgressBar($totalDays);
        while ($date->lt(Carbon::today())){
            InviteStatisticsDailyUpdateByDate::dispatch($date);
            $date->addDay();
            $bar->advance();
        }
        $bar->finish();
        $this->info("\n更新邀请记录统计数据 Finished");

    }
}
