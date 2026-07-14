<?php

namespace Modules\Events\Providers;

use App\Events\AbilitiesEvent;
use App\Events\DashboardEvent;
use Illuminate\Support\Facades\Event;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Modules\Events\Enums\PermissionEnum;
use Nwidart\Modules\Support\ModuleServiceProvider;

class EventsServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Events';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'events';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

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
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));

        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
            $event->menu->addAfter('dashboard', [
                'text' => 'Event Management',
                'url' => route('events::index'),
                'key' => 'event',
                'icon' => 'fas fa-fw fa-calendar-alt',
                'can' => \Modules\Events\Enums\PermissionEnum::VIEW_EVENT->value,
            ]);
            $event->menu->addAfter('event', [
                'text' => 'Event Categories',
                'url' => route('events::categories.index'),
                'key' => 'category',
                'icon' => 'fas fa-fw fa-tags',
                'can' => \Modules\Events\Enums\PermissionEnum::VIEW_EVENT->value,
            ]);
        });

        Event::listen(DashboardEvent::class, function (DashboardEvent $event) {
            $event->addUnits([
                'name' => 'Event Management',
                'url' => route('events::index'),
                'icon' => 'fas fa-fw fa-calendar-alt',
                'can' => \Modules\Events\Enums\PermissionEnum::VIEW_EVENT->value,
            ]);
        });

        Event::listen(AbilitiesEvent::class, function (AbilitiesEvent $event) {
            $event->addUnits([
                PermissionEnum::API_CREATE_EVENT,
                PermissionEnum::API_EDIT_EVENT,
                PermissionEnum::API_VIEW_EVENT,
                PermissionEnum::API_VIEW_EVENT_LIST,
            ]);
        });
    }
}
