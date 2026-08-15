<?php

namespace Modules\Users\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Users\Models\Filter;

class FilterResource extends JsonResource
{
    /**
     * @var Filter
     */
    public $resource;

    public function toArray($request): array
    {
        return [
            'center' => $this->resource?->center,
            'radius' => $this->resource?->radius,
            'categories' => $this->resource?->categories
        ];
    }
}
