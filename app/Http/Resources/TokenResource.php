<?php

namespace App\Http\Resources;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Events\AbilitiesEvent;
use App\Models\UserAPI;
use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    /**
     * @var UserAPI
     */
    public $resource;

    public function toArray($request): array
    {
        return [
            'token' => $this->resource->createToken(RoleEnum::USER->name, $this->abilities())->plainTextToken,
        ];
    }

    public function abilities(): array
    {
        $abilities = new AbilitiesEvent();
        $abilities->addUnits(PermissionEnum::cases());
        event($abilities);

        return $abilities->getUnits();
    }
}
