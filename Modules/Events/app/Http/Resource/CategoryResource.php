<?php

namespace Modules\Events\Http\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Events\Models\Category;

class CategoryResource extends JsonResource
{
    /**
     * @var Category
     */
    public $resource;

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title
        ];
    }
}
