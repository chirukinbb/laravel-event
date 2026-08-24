<?php

namespace Modules\Users\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Events\Repositories\GeoRepository;
use Modules\Users\Http\Requests\FilterRequest;
use Modules\Users\Http\Resources\FilterResource;

class FilterController extends Controller
{
    public function update(FilterRequest $request)
    {
        $valid = $request->validated();
        $tomtom = new GeoRepository($valid['address']);
        $tomtom->geocoding();

        auth()->user()->filter()->update([
            'radius' => $valid['radius'],
            'categories' => $valid['categories'],
            'center' => $tomtom->getPosition()
        ]);

        return FilterResource::make($request->user()->filter);
    }
}