<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

    public function update(Request $request)
    {
        $user = auth()->user()->with('profile')->first();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'country_phone_code' => 'required|string|max:255',
            'country_phone_iso' => 'required|string|max:255',
            'languages' => 'required|array',
            'bio' => 'required|string'
        ]);

        $user->update($validated);

        return redirect()->route('users::profile.index')
            ->with('success', 'Profile updated successfully.');
    }
}
