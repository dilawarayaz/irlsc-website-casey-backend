<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoCallIceCandidate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $fromUserId;
    public $toUserId;
    public $callId;
    public $payload;   // single ICE candidate object {candidate, sdpMid, sdpMLineIndex, ...}

    public function __construct(int $from, int $to, string $callId, array $payload)
    {
        $this->fromUserId = $from;
        $this->toUserId   = $to;
        $this->callId     = $callId;
        $this->payload    = $payload;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->toUserId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'video-call.ice-candidate';
    }
}