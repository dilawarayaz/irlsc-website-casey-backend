<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoCallOffer implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $fromUserId;
    public $toUserId;
    public $callId;
    public $payload;   // SDP offer (object/array from frontend)
    

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
        return 'video-call.offer';
    }

    // Optional: customize the exact data sent to frontend
    // public function broadcastWith(): array
    // {
    //     return [
    //         'from'    => $this->fromUserId,
    //         'callId'  => $this->callId,
    //         'offer'   => $this->payload,
    //     ];
    // }
}