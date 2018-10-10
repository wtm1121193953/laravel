<?php

namespace App\Events;

use App\Modules\Merchant\Merchant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MerchantCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $merchant;
    public $date;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct( Merchant $merchant )
    {
        $this->merchant = $merchant;
        $this->date = date('Y-m-d');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
