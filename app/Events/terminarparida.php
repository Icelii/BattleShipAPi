<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class terminarparida implements ShouldBroadcast
{ 
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $registroid;
    public $ganador;
    public $username;
    /**
     * The ID of the winning player.
     *
     * @var int
     */
    
    /**
     * Create a new event instance.
     *
     * @param int $registroid
     * @param int $ganador
     * @return void
     */
    public function __construct($registroid, $ganador , $username)
    {
        $this->registroid = $registroid;
        $this->ganador = $ganador;
        $this->username = $username;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('finish');
    }
}
