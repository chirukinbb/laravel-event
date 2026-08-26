<?php

namespace Modules\Users\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Users\Http\Requests\FilterRequest;
use Modules\Users\Http\Resources\FilterResource;

class FilterController extends Controller
{
    public function update(FilterRequest $request)
    {
        $valid = $request->validated();

        $user = $request->user();

        $user->filter->update($valid);

        return FilterResource::make($user->filter);
    }
}