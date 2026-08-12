<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Notifications\NewUserNotification;
use Hash;
use Spatie\Permission\Models\Role;

class UserService
{
    public function signup(
        string $name,
        string $email,
        string $password
    )
    {
        // Create user with generated password
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password)
        ]);

        // Assign default User role
        $userRole = Role::where('name', RoleEnum::USER->value)->first();
        if ($userRole) {
            $user->assignRole($userRole);
        }

        try {
            $user->notify(new NewUserNotification($password));

            return $user;
        } catch (\Exception $e) {
            $user->delete();

            return $e;
        }
    }
}