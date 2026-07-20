<?php

namespace Modules\Events\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Events\Http\Requests\EventRequest;
use Modules\Events\Http\Resource\EventResource;
use Modules\Events\Jobs\GeocodeJob;
use Modules\Events\Jobs\NewEventNotificationJob;
use Modules\Events\Models\Event;
use Modules\Events\Services\TagService;

class EventsController extends Controller
{
    public function index()
    {
        $events = Event::paginate();

        return EventResource::collection($events);
    }

    public function show(Event $event)
    {
        return EventResource::make($event);
    }

    public function store(EventRequest $request)
    {
        $eventModel = Event::getModel();

        $eventModel->title = $request->title;
        $eventModel->description = $request->description;
        $eventModel->thumbnail_url = $request->thumbnail->storePublicly('public/events/thumbnails');
        $eventModel->category_id = $request->category_id;
        $eventModel->planing_time = $request->planing_time;
        $eventModel->user_id = $request->user_id;
        $eventModel->slots = $request->slots;
        $eventModel->address = $request->address;

        $eventModel->save();

        GeocodeJob::dispatch($eventModel->id);
        NewEventNotificationJob::dispatch($eventModel->id);

        $tagService = new TagService($eventModel);

        $tagService->action($request->tags);

        return response()->json([
            'message' => 'Event created successfully'
        ], 201);
    }

    public function update(EventRequest $request, Event $event)
    {
        $event->title = $request->title;
        $event->description = $request->description;
        $event->category_id = $request->category_id;
        $event->planing_time = $request->planing_time;
        $event->user_id = $request->user_id;
        $event->slots = $request->slots;
        $event->address = $request->address;

        if ($request->thumbnail) {
            $event->thumbnail_url = $request->thumbnail->storePublicly('public/events/thumbnails');
        }

        $event->save();

        $tagService = new TagService($event);

        $tagService->action($request->tags);

        return response()->json([
            'message' => 'Event updated successfully'
        ], 200);
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ], 200);
    }
}