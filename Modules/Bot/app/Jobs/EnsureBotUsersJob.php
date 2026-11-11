<?php

namespace Modules\Bot\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;

class EnsureBotUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $count = 0)
    {
    }

    public function handle(): void
    {
        if ($this->count <= 0) {
            return;
        }

        $existing = User::count();
        $toCreate = max(0, $this->count - $existing);

        if ($toCreate <= 0) {
            return;
        }

        for ($i = 0; $i < $toCreate; $i++) {
            User::factory()->create([
                'email' => sprintf('bot-user-%s@example.com', uniqid()),
                'password' => Hash::make('secret123'),
            ]);
        }
    }
}
