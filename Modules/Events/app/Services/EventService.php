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

        $event = Event::create($data);

        NewEventNotificationJob::dispatch($event->id);

        $tagService = new TagService($event);

        $tagService->action($event->tags);
    }

    public function update(array $data, Event $event)
    {
        if (isset($data['thumbnail'])) {
            $data['thumbnail_url'] = asset($data['thumbnail']->store('thumbnails', 'public'));
            unset($data['thumbnail']);
        }

        $event->update($data);

        UpdateEventNotificationJob::dispatch($event->id);

        $tagService = new TagService($event);

        $tagService->action($event->tags);
    }
}