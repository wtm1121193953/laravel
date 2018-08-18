<?php

namespace App\Console\Commands\Updates;

use App\Modules\Order\OrderService;
use App\Modules\User\User;
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
        //
        $this->info('准备更新用户下单总次数');
        $bar = $this->output->createProgressBar(User::count('id'));
        User::chunk(1000, function($list) use ($bar){
            $list->map(function(User $user) use ($bar){
                $user->order_count = OrderService::getOrderCountByUserId($user->id);
                $user->save();
                $bar->advance();
            });
        });
        $bar->finish();
    }
}
