<?php

namespace Modules\Events\Http\Resource;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Events\Models\Event;

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
            'data' => $this->collection->map(function (Event $event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'thumbnail_url' => $event->thumbnail_url,
                    'category' => $event->category->title,
                    'description' => $event->description,
                    'slots' => $event->slots,
                    'reserved' => $event->withCount('members')->members_count,
                    'planing_time' => Carbon::parse($event->planing_time)->timestamp,
                ];
            }),
        ];
    }
}
