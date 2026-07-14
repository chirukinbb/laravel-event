<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Laravel\Socialite\Socialite;

class AuthController extends Controller
{
    public function index(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function entry(string $provider)
    {
        if ($provider == 'manual') {
            return $this->manualLogin();
        }

        $user = Socialite::driver($provider)->user();

        if (User::where('email', $user->email)->exists()) {
            $user = User::where('email', $user->email)->first();
        } else {
            User::create([
                'name' => $user->name,
                'email' => $user->email,
                'provider' => $provider,
                'provider_id' => $user->id,
            ]);
        }

        return UserResource::make($user);
    }

    public function manualLogin()
    {
        $request = request();
    }
}