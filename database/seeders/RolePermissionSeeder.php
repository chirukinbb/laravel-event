<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\UserAPI;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $viewUsersPermission = Permission::firstOrCreate(['name' => PermissionEnum::VIEW_USERS->value]);
        $editUserRolePermission = Permission::firstOrCreate(['name' => PermissionEnum::EDIT_USER_ROLE->value]);
        $createUserPermission = Permission::firstOrCreate(['name' => PermissionEnum::CREATE_USER->value]);
        $viewSettingsPermission = Permission::firstOrCreate(['name' => PermissionEnum::VIEW_SETTINGS->value]);

        // Create roles
        $masterRole = Role::firstOrCreate(['name' => RoleEnum::MASTER->value]);
        $userRole = Role::firstOrCreate([
            'name' => RoleEnum::USER->value,
            'guard_name' => 'api'
        ]);

        // Assign permissions to master role
        $masterRole->givePermissionTo([
            $viewUsersPermission,
            $editUserRolePermission,
            $createUserPermission,
            $viewSettingsPermission,
        ]);

        // Create master user if not exists
        $masterUser = User::firstOrCreate(
            ['email' => 'master@example.com'],
            [
                'name' => 'Master User',
                'password' => Hash::make('master123'),
            ]
        );
        $masterUser->assignRole($masterRole);

        // Create regular user if not exists
        $regularUser = UserAPI::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('user123'),
            ]
        );
        $regularUser->assignRole($userRole);
    }
}
