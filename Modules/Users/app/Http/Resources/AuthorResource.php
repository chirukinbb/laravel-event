<?php

namespace Modules\Users\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Users\Models\Profile;

class AuthorResource extends JsonResource
{
    /**
     * @var Profile
     */
    public $resource;

    public function toArray($request): array
    {
        return [
            'name' => $this->resource?->name,
            'languages' => $this->resource?->languages,
            'bio' => $this->resource?->bio,
        ];
    }
}
