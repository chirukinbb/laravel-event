<?php

namespace Modules\Users\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
        ]);

        auth()->user()->update([
            'fcm_token' => $validated['fcm_token'],
        ]);

        return response()->json([
            'message' => 'Device token updated.',
        ]);
    }
}
