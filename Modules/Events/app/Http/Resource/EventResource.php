<?php

namespace Modules\Events\Http\Resource;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Events\Events\EventResourceEvent;
use Modules\Events\Models\Event;
use Modules\Events\Models\Member;
use Modules\Events\Models\Tag;
use Modules\Users\Http\Resources\ProfileResource;

class EventResource extends JsonResource
{
    /**
     * @var Event
     */
    public $resource;

    public function __construct($resource)
    {
        $relations = ['category', 'tags'];

        if (request()->user()?->id === $resource->user_id) {
            $relations[] = 'members';
        }

        $resource->loadMissing($relations);

        if (is_null($resource->getAttributeValue('members_count'))) {
            $resource->loadCount('members');
        }

        parent::__construct($resource);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $event = new EventResourceEvent($this->resource);
        $member = $this->resource->members->firstWhere('user_id', $request->user()?->id);

        $event->addUnit('id', $this->resource->id);
        $event->addUnit('title', $this->resource->title);
        $event->addUnit('category', $this->resource->category->title);
        $event->addUnit('thumbnail_url', asset($this->resource->thumbnail_url));
        $event->addUnit('description', $this->resource->description);
        $event->addUnit('coordinate_lat', $this->resource->coordinate_lat);
        $event->addUnit('coordinate_lng', $this->resource->coordinate_lng);
        $event->addUnit('country', config('events.countries.' . $this->resource->country_iso, $this->resource->country_iso));
        $event->addUnit('planing_time', Carbon::parse($this->resource->planing_time)->timestamp);
        $event->addUnit('slots', $this->resource->slots);
        $event->addUnit('tags', $this->resource->tags->map(fn(Tag $tag) => $tag->name));
        $event->addUnit('reserved', $this->resource->members_count);

        if ($member?->id) {
            $event->addUnit('member', $member?->id);
        }

        if ($request->user()->id === $this->resource->user_id) {
            $event->addUnit('members', $this->getMembers());
        }

        event($event);

        return $event->getUnits();
    }

    private function getMembers(): array
    {
        $members = collect();

        $this->resource->members->map(function (Member $member) use (&$members) {
            $members->push([
                'id' => $member->id,
                'profile' => ProfileResource::make($member->user->profile)
            ]);
        });


        return $members->toArray();
    }
}
