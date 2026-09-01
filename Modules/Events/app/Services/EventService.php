<?php

namespace Modules\Events\Services;

use Modules\Events\Jobs\NewEventNotificationJob;
use Modules\Events\Jobs\UpdateEventNotificationJob;
use Modules\Events\Models\Event;

class EventService
{
    public function store(array $data)
    {
        $data['thumbnail_url'] = asset($data['thumbnail']->store('thumbnails', 'public'));
        $data['coordinate_lat'] = $data['address'][0];
        $data['coordinate_lng'] = $data['address'][1];
        unset($data['address']);

        $event = Event::create($data);

        NewEventNotificationJob::dispatch($event->id);

        $tagService = new TagService($event);

        $tagService->action($event->tags->toArray());

        return $event;
    }

    public function update(array $data, Event $event)
    {
        $data['coordinate_lat'] = $data['address'][0];
        $data['coordinate_lng'] = $data['address'][1];
        unset($data['address']);

        if (isset($data['thumbnail'])) {
            $data['thumbnail_url'] = asset($data['thumbnail']->store('thumbnails', 'public'));
            unset($data['thumbnail']);
        }

        $event->update($data);

        UpdateEventNotificationJob::dispatch($event->id);

        $tagService = new TagService($event);

        $tagService->action($event->tags->toArray());

        return $event;
    }
}