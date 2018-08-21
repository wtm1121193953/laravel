<?php

namespace App\Jobs;

use App\Modules\Order\OrderService;
use App\Modules\User\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UserOrderCountUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;

    /**
     * Create a new job instance.
     *
     * @param $userId
     */
    public function __construct($userId)
    {
        //
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $user = User::find($this->userId);
        if($user){
            $user->order_count = OrderService::getOrderCountByUserId($user->id);
            $user->save();
        }
    }
}
