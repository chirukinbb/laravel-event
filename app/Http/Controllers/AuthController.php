<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\UserAPI;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    // Show login form
    public function showLoginForm()
    {
        return view("auth.login");
    }

    // Handle login request with static credentials (demo only)
    public function login(LoginRequest $request)
    {
        if (Auth::attempt(['email' => $request->post('email'), 'password' => $request->post('password')], true)) {
            $user = Auth::user();

            // Auto-verify email on first login if not verified
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
                $user->save();
            }

            // Assign default User role if user has no roles
            if ($user->roles->count() === 0) {
                $userRole = Role::where('name', RoleEnum::USER->value)->first();
                if ($userRole) {
                    $user->assignRole($userRole);
                }
            }

            return redirect()->intended("/dashboard");
        }

        return back()->withErrors([
            "email" => "The provided credentials do not match our records.",
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $password = Str::random(12);

        try {
            (new UserService())->signup($request->name, $request->email, $password);

            return redirect()->route('login')
                ->with('success', 'Registration successful! Please check your email for the password.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Failed to send registration email. Please try again later.'
            ]);
        }
    }

    public function logout()
    {
        Auth::logout();

        return redirect("/login");
    }

    function socialLogin(string $provider, Request $request)
    {
        $source = $request->get('source', 'web');
        $state = json_encode(['source' => $source]);

        return Socialite::driver($provider)
            ->stateless()
            ->with(['state' => base64_encode($state), 'prompt' => 'select_account'])
            ->redirect();
    }

    function socialEntry(string $provider, Request $request)
    {
        $source = 'web';
        if ($request->has('state')) {
            $stateData = json_decode(base64_decode($request->get('state')), true);
            $source = $stateData['source'] ?? 'web';
        }

        $user = Socialite::driver($provider)->stateless()->user();
        $class = $source === 'web' ? 'App\Models\User' : UserAPI::class;

        if ($class::where('email', $user->email)->exists()) {
            $user = $class::where('email', $user->email)->first();
        } else {
            $password = Str::random(12);
            $user = (new UserService())->signup($user->name, $user->email, $password, $source);
        }

        $user->profile->update([
            'avatar_url' => $user->avatar,
        ]);

        if ($source === 'web') {
            Auth::login($user);
            return redirect()->route('dashboard');
        }

        return redirect()->away(env('DEEP_LINK') . "?token={$user->createToken(RoleEnum::USER->name)->plainTextToken}");
    }
}
