<?php

namespace Modules\Users\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Users\Http\Requests\ProfileRequest;
use Modules\Users\Http\Resources\ProfileResource;

class ProfileController extends Controller
{
    public function update(ProfileRequest $request)
    {
        $user = auth()->user()->with('profile')->first();

        $validated = $request->validated();

        (new \ProfileService($user))->update($validated);

        return ProfileResource::make($user->profile);
    }
}