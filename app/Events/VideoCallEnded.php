<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoCallEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $fromUserId;
    public $toUserId;
    public $callId;

    public function __construct(int $from, int $to, string $callId)
    {
        $this->fromUserId = $from;
        $this->toUserId   = $to;
        $this->callId     = $callId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->toUserId}"),
            // Optional: also broadcast to caller so UI updates both sides cleanly
            new PrivateChannel("user.{$this->fromUserId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'video-call.ended';
    }
}