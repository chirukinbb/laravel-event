<?php

namespace Modules\Users\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Users\Http\Requests\ProfileRequest;

class ProfileController extends Controller
{
    public function update(ProfileRequest $request)
    {
        $user = auth()->user()->with('profile')->first();

        $validated = $request->validated();

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.'
        ]);
    }
}