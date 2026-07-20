<?php

namespace Modules\Events\Events;

use App\Traits\ModularResource;
use Modules\Events\Models\Event;

class EventResourceEvent
{
    use ModularResource;

    /**
     * @var Event
     */
    public $resource;
}