<?php

namespace Modules\Events\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\User;
use Modules\Events\Http\Requests\EventRequest;
use Modules\Events\Jobs\GeocodeJob;
use Modules\Events\Jobs\NotificationJob;
use Modules\Events\Models\Category;
use Modules\Events\Models\Event;
use Modules\Events\Models\Tag;
use Modules\Events\Services\TagService;

class EventsController extends Controller
{
    public function index()
    {
        $events = Event::paginate(15);

        return view('events::events.index', compact('events'));
    }

    public function create()
    {
        $categories = Category::all();
        $users = User::all();
        $tags = Tag::all();

        return view('events::events.create', compact(
            'users',
            'categories',
            'tags'
        ));
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
        NotificationJob::dispatch($eventModel->id);

        $tagService = new TagService($eventModel);

        $tagService->action($request->tags);

        return redirect()->route('events::index')->with('success', 'Event created!');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $event = Event::find($id);
        $categories = Category::all();
        $users = User::all();
        $tags = Tag::all();

        return view('events::events.edit', compact(
            'event',
            'users',
            'categories',
            'tags'
        ));
    }

    public function update(EventRequest $request, $id)
    {
        $eventModel = Event::find($id);

        $eventModel->title = $request->title;
        $eventModel->description = $request->description;
        $eventModel->category_id = $request->category_id;
        $eventModel->planing_time = $request->planing_time;
        $eventModel->user_id = $request->user_id;
        $eventModel->slots = $request->slots;
        $eventModel->address = $request->address;

        if ($request->thumbnail) {
            $eventModel->thumbnail_url = $request->thumbnail->storePublicly('public/events/thumbnails');
        }

        $eventModel->save();

        $tagService = new TagService($eventModel);

        $tagService->action($request->tags);

        return redirect()->route('events::index')->with('success', 'Event updated!');
    }

    public function destroy($id)
    {
        Event::find($id)->delete();

        return redirect()->route('events::index')->with('success', 'Event deleted!');
    }
}
