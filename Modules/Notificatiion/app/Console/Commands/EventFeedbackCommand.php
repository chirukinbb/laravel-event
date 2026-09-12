<?php

namespace Modules\Notification\Console\Commands;

use Illuminate\Console\Command;
use Modules\Events\Models\Event;
use Modules\Events\Models\Member;
use Modules\Notificatiion\Models\Notification;
use Modules\Notification\Notifications\EventFeedbackNotification;

class EventFeedbackCommand extends Command
{
    protected $signature = 'event:feedback';

    protected $description = 'Command description';

    public function handle()
    {
        Event::where('is_happened', true)
            ->whereBetween('planing_time', [now()->startOfDay(), now()->subDays(2)->endOfDay()])
            ->chunkById(10, function (Event $event) {
                $event->members()->chunkById(10, function (Member $member) use ($event) {
                    $member->user->notify(new EventFeedbackNotification($event));
                    Notification::create([
                        'user_id' => $member->user->id,
                        'type' => 'event',
                        'data' => ['screen' => 'event_feedback', 'event_id' => $event->id]
                    ]);
                });
            });
    }
}
