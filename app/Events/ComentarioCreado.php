<?php

namespace App\Events;

use App\Models\Comentario;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComentarioCreado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comentario;

    public function __construct(Comentario $comentario)
    {
        $this->comentario = $comentario->load('user');
    }

    //----channel privado: solo staff, el dueño del ticket o el tecnico asignado pueden escuchar (ver routes/channels.php)
    public function broadcastOn()
    {
        return new PrivateChannel('ticket.' . $this->comentario->ticket_id);
    }

    public function broadcastAs(){
        return 'comentario.creado';
    }
}
