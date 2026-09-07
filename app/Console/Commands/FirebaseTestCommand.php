<?php

namespace App\Console\Commands;


use App\Models\User;
use Illuminate\Console\Command;
use Modules\Events\Models\Event;
use Modules\Events\Notifications\EventNotification;

class FirebaseTestCommand extends Command
{
    protected $signature = 'firebase';

    protected $description = 'Command description';

    public function handle()
    {
        $user = User::where('email', 'chirukin@gmail.com')->first();
        $event = Event::first();

        $user->notify(new EventNotification($event));
    }
}
