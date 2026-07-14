<?php

namespace App\Events;

use App\Traits\HasCollection;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AbilitiesEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels, HasCollection;
}
