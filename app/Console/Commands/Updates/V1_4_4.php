<?php

namespace App\Console\Commands\Updates;

use App\Jobs\UserInviteChannelsDataMigration;
use App\Modules\Invite\InviteChannel;
use Illuminate\Console\Command;

class V1_4_4 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:v1.4.4';

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
     * @throws \Exception
     */
    public function handle()
    {
        // 修复旧的邀请渠道数据

        $this->info('修复旧的邀请渠道数据 Start');
        $bar = $this->output->createProgressBar(
            InviteChannel::where('origin_type', InviteChannel::ORIGIN_TYPE_USER)
                ->where('oper_id', '<>', 0)
                ->count('id')
        );
        InviteChannel::where('origin_type', InviteChannel::ORIGIN_TYPE_USER)
            ->where('oper_id', '<>', 0)
            ->select(['origin_id', 'origin_type'])
            ->groupBy(['origin_id', 'origin_type'])
            ->orderBy('origin_id')
            ->chunk(10000, function ( $channels ) use ($bar) {
                $channels->each(function ( $channel ) use ($bar) {
                    UserInviteChannelsDataMigration::dispatch( $channel->origin_id, $channel->origin_type );
                    $bar->advance();
                });
            });
        $bar->finish();
        $this->info("\n修复旧的邀请渠道数据 End");
    }
}
