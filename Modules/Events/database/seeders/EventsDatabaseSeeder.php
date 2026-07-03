<?php

namespace Modules\Events\Database\Seeders;

use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Modules\Events\Enums\PermissionEnum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EventsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $masterRole = Role::where(['name' => RoleEnum::MASTER->value])->first();
        $userRole = Role::where(['name' => RoleEnum::USER->value])->first();

        Permission::create(['name' => PermissionEnum::VIEW_EVENT->value])->assignRole($masterRole);
        Permission::create(['name' => PermissionEnum::CREATE_EVENT->value])->assignRole($masterRole);
        Permission::create(['name' => PermissionEnum::EDIT_EVENT->value])->assignRole($masterRole);

        Permission::create([
            'name' => PermissionEnum::API_VIEW_EVENT->value,
            'guard_name' => 'api'
        ])->assignRole($userRole);
        Permission::create([
            'name' => PermissionEnum::API_CREATE_EVENT->value,
            'guard_name' => 'api'
        ])->assignRole($userRole);
        Permission::create([
            'name' => PermissionEnum::API_EDIT_EVENT->value,
            'guard_name' => 'api'
        ])->assignRole($userRole);
    }
}
