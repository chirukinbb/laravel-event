<?php

namespace Modules\Events\Http\Resource;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Events\Models\Event;

/** @see \Modules\Events\Models\Event */
class EventCollection extends JsonResource
{
    /**
     * @var Event
     */
    public $resource;

    /**
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'thumbnail_url' => $this->resource->thumbnail_url,
            'category' => $this->resource->category->title,
            'description' => $this->resource->description,
            'slots' => $this->resource->slots,
            'reserved' => $this->resource->members_count,
            'planing_time' => Carbon::parse($this->resource->planing_time)->timestamp,
        ];
    }
}
