<?php

namespace App\Events;

use App\Traits\HasCollection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DashboardEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels, HasCollection;

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }

    public function getUnits(): array
    {
        return $this->collection->toArray();
    }

    public function addUnits(mixed $unit)
    {
        $this->collection->push($unit);
    }
}
