<?php

namespace Modules\Events\Services;

use Illuminate\Support\Facades\Log;
use Modules\Events\Jobs\NewEventNotificationJob;
use Modules\Events\Jobs\UpdateEventNotificationJob;
use Modules\Events\Models\Event;

class EventService
{
    public function store(array $data)
    {
        try {
            $data['thumbnail_url'] = asset($data['thumbnail']->store('thumbnails', 'public'));
            $data['coordinate_lat'] = $data['address'][0];
            $data['coordinate_lng'] = $data['address'][1];
            unset($data['address']);

            $event = Event::create($data);

            NewEventNotificationJob::dispatch($event->id);

            $tagService = new TagService($event);
            $tagService->action($event->tags->toArray());

            // 1. Успешное создание события
            Log::channel('userlog')->info('Event created successfully', [
                'user_id' => auth()->id(),
                'event_id' => $event->id,
                'title' => $event->title ?? null,
                'coordinates' => [
                    'lat' => $data['coordinate_lat'],
                    'lng' => $data['coordinate_lng'],
                ],
            ]);

            return $event;

        } catch (\Throwable $e) {
            // 2. Логирование ошибки при создании
            Log::channel('userlog')->error('Failed to create event', [
                'user_id' => auth()->id(),
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Пробрасываем далее для обработки контроллером/Handler
        }
    }

    public function update(array $data, Event $event)
    {
        try {
            if (isset($data['address'])) {
                $data['coordinate_lat'] = $data['address'][0];
                $data['coordinate_lng'] = $data['address'][1];
                unset($data['address']);
            }

            if (isset($data['thumbnail'])) {
                $data['thumbnail_url'] = asset($data['thumbnail']->store('thumbnails', 'public'));
                unset($data['thumbnail']);
            }

            // Фиксируем исходные значения для аудита изменений
            $oldData = $event->only(array_keys($data));

            $event->update($data);

            UpdateEventNotificationJob::dispatch($event->id);

            $tagService = new TagService($event);
            $tagService->action($event->tags->toArray());

            // 3. Успешное обновление события (с фиксацией изменений)
            Log::channel('userlog')->info('Event updated successfully', [
                'user_id' => auth()->id(),
                'event_id' => $event->id,
                'changes' => $event->getChanges(), // Показывает только изменившиеся поля
                'old_data' => $oldData,
            ]);

            return $event;

        } catch (\Throwable $e) {
            // 4. Логирование ошибки при обновлении
            Log::channel('userlog')->error('Failed to update event', [
                'user_id' => auth()->id(),
                'event_id' => $event->id,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}