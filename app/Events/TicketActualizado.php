<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TicketActualizado implements ShouldBroadcastNow 
{
    use Dispatchable, SerializesModels;

    public function __construct() {}

    public function broadcastOn(): array
    {
        return [
            new Channel('tickets-publicos'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'TicketActualizado';
    }
}
