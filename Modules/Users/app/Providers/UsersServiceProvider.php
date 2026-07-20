<?php

namespace Modules\Users\Providers;

use App\Events\DashboardEvent;
use App\Events\SettingsEvent;
use App\Events\UserResourceEvent;
use Illuminate\Support\Facades\Event;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Modules\Events\Events\EventResourceEvent;
use Modules\Users\Enums\SettingsEnum;
use Modules\Users\Http\Resources\AuthorResource;
use Modules\Users\Http\Resources\FilterResource;
use Modules\Users\Http\Resources\ProfileResource;
use Nwidart\Modules\Support\ModuleServiceProvider;

class UsersServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Users';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'users';

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
            $event->menu->addBefore('settings', [
                'text' => 'User Management',
                'url' => route('users::index'),
                'key' => 'users',
                'icon' => 'fas fa-fw fa-users',
                'can' => \App\Enums\PermissionEnum::VIEW_USERS->value,
            ]);
            $event->menu->add([
                'text' => 'My Profile',
                'url' => route('users::profile.index'),
                'icon' => 'fas fa-fw fa-user',
                'classes' => 'text-center',
                'topnav_user' => true
            ]);
            $event->menu->add([
                'text' => 'Change Password',
                'url' => route('password.request'),
                'icon' => 'fas fa-fw fa-user',
                'classes' => 'text-center',
                'topnav_user' => true,
            ]);
        });

        Event::listen(DashboardEvent::class, function (DashboardEvent $event) {
            $event->addUnits([
                'name' => 'My Profile',
                'url' => route('users::profile.index'),
                'icon' => 'fas fa-fw fa-user',
                'can' => \App\Enums\PermissionEnum::VIEW_USERS->value,
            ]);
            $event->addUnits([
                'name' => 'User Management',
                'url' => route('users::index'),
                'icon' => 'fas fa-fw fa-users',
                'can' => \App\Enums\PermissionEnum::VIEW_USERS->value,
            ]);
        });

        Event::listen(SettingsEvent::class, function (SettingsEvent $event) {
            $event->addUnits(SettingsEnum::cases());
        });

        Event::listen(UserResourceEvent::class, function (UserResourceEvent $event) {
            $event->addUnit('profile', ProfileResource::make(auth()->user()->profile));
            $event->addUnit('filter', FilterResource::make(auth()->user()->filter));
        });

        Event::listen(EventResourceEvent::class, function (EventResourceEvent $event) {
            $event->addUnit('author', AuthorResource::make(auth()->user()->profile));
        });
    }
}
