<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\TokenResource;
use App\Http\Resources\UserResource;
use App\Models\UserAPI;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $user = UserAPI::where('email', $credentials['email'])->first();

        // 1. Неудачная попытка входа (неверный email или пароль)
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            Log::channel('userlog')->warning('Failed login attempt', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'reason' => !$user ? 'User not found' : 'Invalid password',
            ]);

            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        // 2. Успешный вход
        Log::channel('userlog')->info('User logged in successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return TokenResource::make($user);
    }

    public function register(RegisterRequest $request)
    {
        $password = Str::random(12);

        try {
            (new UserService())->signup($request->name, $request->email, $password, 'api');

            // 1. Успешная регистрация
            Log::channel('userlog')->info('User registered successfully', [
                'name' => $request->name,
                'email' => $request->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json(['message' => 'Registration successful! Please check your email for the password.'], 200);
        } catch (\Exception $e) {
            // 2. Ошибка при регистрации (ошибка сервиса, базы данных, отправки письма и т.д.)
            Log::channel('userlog')->error('User registration failed', [
                'name' => $request->name,
                'email' => $request->email,
                'ip' => $request->ip(),
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Опционально для трассировки
            ]);

            return response()->json(['message' => 'Try later'], 401);
        }
    }

    public function me(Request $request)
    {
        return UserResource::make($request->user());
    }
}