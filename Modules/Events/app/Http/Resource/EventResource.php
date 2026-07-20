<?php

namespace Modules\Events\Http\Resource;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Events\Events\EventResourceEvent;
use Modules\Events\Models\Event;

class EventResource extends JsonResource
{
    /**
     * @var Event
     */
    public $resource;

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $event = new EventResourceEvent();

        $event->addUnit('title', $this->resource->title);
        $event->addUnit('category', $this->resource->category->title);
        $event->addUnit('thumbnail_url', asset($this->resource->thumbnail_url));
        $event->addUnit('description', $this->resource->description);
        $event->addUnit('coordinate_lat', $this->resource->coordinate_lat);
        $event->addUnit('coordinate_lng', $this->resource->coordinate_lng);
        $event->addUnit('country', config('events.countries.' . $this->resource->country_iso, $this->resource->country_iso));
        $event->addUnit('planing_time', Carbon::parse($this->resource->planing_time)->timestamp);
        $event->addUnit('slots', $this->resource->slots);
        $event->addUnit('address', $this->resource->address);
        $event->addUnit('reserved', $this->resource->members->count());

        event($event);

        return $event->getUnits();
    }
}
