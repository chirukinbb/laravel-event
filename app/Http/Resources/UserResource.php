<?php

namespace App\Http\Resources;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Events\AbilitiesEvent;
use App\Events\UserResourceEvent;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @var User
     */
    public $resource;

    public function toArray($request): array
    {
        $userResourceEvent = new UserResourceEvent();

        $userResourceEvent->addUnit('name', $this->resource->name);
        $userResourceEvent->addUnit('token', $this->resource->createToken(RoleEnum::USER->name, $this->abilities())->plainTextToken);

        event($userResourceEvent);

        return $userResourceEvent->getUnits();
    }

    public function abilities(): array
    {
        $abilities = new AbilitiesEvent();
        $abilities->addUnits(PermissionEnum::cases());
        event($abilities);

        return $abilities->getUnits();
    }
}
