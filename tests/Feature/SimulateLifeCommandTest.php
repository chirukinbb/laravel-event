<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Modules\Bot\Jobs\EnsureBotUsersJob;
use Modules\Bot\Jobs\SimulateEventActivityJob;
use Tests\TestCase;

class SimulateLifeCommandTest extends TestCase
{
    public function test_simulate_life_command_dispatches_queue_jobs(): void
    {
        Queue::fake();

        $this->artisan('bot:simulate-life', [
            '--events' => 1,
            '--subscribers' => 2,
            '--messages' => 1,
            '--delay' => 0,
            '--fresh-users' => 3,
        ])->assertSuccessful();

        Queue::assertPushed(EnsureBotUsersJob::class);
        Queue::assertPushed(SimulateEventActivityJob::class);
    }
}
