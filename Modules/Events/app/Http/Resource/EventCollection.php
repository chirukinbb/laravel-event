<?php

namespace Modules\Events\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \Modules\Events\Models\Event */
class EventCollection extends ResourceCollection
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function (EventResource $event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'thumbnail_url' => $event->thumbnail_url,
                    'category' => $event->category->title,
                    'description' => $event->description,
                    'slots' => $event->slots,
                    'reserved' => $event->reserved,
                    'planing_time' => $event->planing_time
                ];
            }),
        ];
    }
}
