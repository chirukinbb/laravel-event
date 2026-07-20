<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Users\Http\Requests\ProfileRequest;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user()->with('profile')->first();
        return view('users::profile.index', compact('user'));
    }

    public function edit()
    {
        $user = auth()->user()->with('profile')->first();
        return view('users::profile.edit', compact('user'));
    }

    public function update(ProfileRequest $request)
    {
        $user = auth()->user()->with('profile')->first();

        $validated = $request->validated();

        $user->update($validated);

        return redirect()->route('users::profile.index')
            ->with('success', 'Profile updated successfully.');
    }
}
