<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Artisan::call('db:wipe');
        \Artisan::call('migrate');
        \Artisan::call('db:seed');
        \Artisan::call('module:seed Events');

        $this->info('Setup command executed successfully.');
    }
}
