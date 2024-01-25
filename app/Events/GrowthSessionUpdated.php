<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GrowthSessionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $originalGrowthSessionAttributes;
    public array $newGrowthSessionAttributes;

    public function __construct(array $originalGrowthSessionAttributes, array $newGrowthSessionAttributes)
    {
        $this->originalGrowthSessionAttributes = $originalGrowthSessionAttributes;
        $this->newGrowthSessionAttributes = $newGrowthSessionAttributes;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('growth-session-updated');
    }
}
