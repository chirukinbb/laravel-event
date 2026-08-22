<?php

namespace App\Http\Resources;

use App\Events\UserResourceEvent;
use App\Models\UserAPI;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @var UserAPI
     */
    public $resource;

    public function toArray($request): array
    {
        $userResourceEvent = new UserResourceEvent($this->resource);

        $userResourceEvent->addUnit('name', $this->resource->name);

        event($userResourceEvent);

        return $userResourceEvent->getUnits();
    }
}
