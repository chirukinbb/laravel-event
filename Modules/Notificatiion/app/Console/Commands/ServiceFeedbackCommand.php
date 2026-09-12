<?php

namespace Modules\Notification\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Modules\Notificatiion\Models\Notification;
use Modules\Notification\Notifications\ServiceFeedbackNotification;

class ServiceFeedbackCommand extends Command
{
    protected $signature = 'service:feedback';

    protected $description = 'Command description';

    public function handle(): void
    {
        $upcomingThreshold = now()->addDay();

        User::whereDoesntHave('feedback')
            ->whereHas('events', function ($query) use ($upcomingThreshold) {
                $query->where('planing_time', '<', $upcomingThreshold);
            })
            ->orWhereHas('members', function ($query) use ($upcomingThreshold) {
                $query->whereRelation('events', 'planing_time', '<', $upcomingThreshold);
            })
            ->distinct()
            ->chunkById(10, function ($users) {
                foreach ($users as $user) {
                    $user->notify(new ServiceFeedbackNotification());
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'service',
                        'data' => ['screen' => 'service_feedback']
                    ]);
                }
            });
    }
}
