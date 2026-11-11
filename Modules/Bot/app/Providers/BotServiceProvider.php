<?php

namespace Modules\Bot\Providers;

use Modules\Bot\Console\Commands\SimulateLifeCommand;
use Nwidart\Modules\Support\ModuleServiceProvider;

class BotServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Bot';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'bot';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [SimulateLifeCommand::class];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
