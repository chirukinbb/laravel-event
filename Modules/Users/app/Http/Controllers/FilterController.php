<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Events\Models\Category;
use Modules\Events\Repositories\GeoRepository;

class FilterController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $categories = Category::all();
        $geo = GeoRepository::reverseGeocoding($user->filter->center[0], $user->filter->center[1]);
        $address = $geo->address;

        return view('users::profile.filter', compact('user', 'categories', 'address'));
    }

    public function update(\Illuminate\Http\Request $request)
    {
        $valid = $request->validate([
            'address' => 'string|required',
            'radius' => 'numeric|required',
            'categories' => 'array|required|min:1',
        ]);

        $tomtom = new GeoRepository($valid['address']);
        $tomtom->geocoding();

        auth()->user()->filter()->update([
            'radius' => $valid['radius'],
            'categories' => $valid['categories'],
            'center' => $tomtom->getPosition()
        ]);

        return redirect()->route('users::profile.index')
            ->with('success', 'Filter updated successfully.');
    }
}