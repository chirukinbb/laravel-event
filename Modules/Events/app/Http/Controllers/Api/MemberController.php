<?php

namespace Modules\Events\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Events\Http\Requests\FeedbackRequest;
use Modules\Events\Http\Resource\EventResource;
use Modules\Events\Jobs\EventUpdatedNotificationJob;
use Modules\Events\Models\Event;
use Modules\Events\Models\Member;

class MemberController extends Controller
{
    public function index(Event $event)
    {

    }

    public function create(Event $event)
    {
        $event->members()->create([
            'user_id' => auth()->id()
        ]);
        EventUpdatedNotificationJob::dispatch($event->id);

        return EventResource::make($event);
    }

    public function update(FeedbackRequest $request, Event $event, Member $member)
    {
        $member->update([
            'is_happened' => $request->is_happened,
            'comment' => $request->comment,
            'mark' => $request->mark
        ]);
        EventUpdatedNotificationJob::dispatch($event->id);

        return response()->json([
            'message' => 'Feedback created successfully'
        ]);
    }

    public function destroy(Event $event, Member $member)
    {
        $member->delete();
        EventUpdatedNotificationJob::dispatch($event->id);

        return EventResource::make($event->load(['members', 'category', 'tags'])->loadCount('members'));
    }
}