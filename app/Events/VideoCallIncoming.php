<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class VideoCallIncoming implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $fromUserId;
    public $toUserId;
    public $callId;           // uuid or timestamp+user combo

    public function __construct($from, $to, $callId)
    {
        $this->fromUserId = $from;
        $this->toUserId   = $to;
        $this->callId     = $callId;
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("user.{$this->toUserId}")];
    }

    public function broadcastAs()
    {
        return 'video-call.incoming';
    }
}