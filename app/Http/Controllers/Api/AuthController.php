<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return UserResource::make($user);
    }

    public function register(RegisterRequest $request)
    {
        $password = Str::random(12);

        try {
            (new UserService())->signup($request->name, $request->email, $password);

            return response()->json(['message' => 'Registration successful! Please check your email for the password.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Try later'], 401);
        }
    }

    public function me(Request $request)
    {
        return UserResource::make($request->user());
    }
}