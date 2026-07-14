<?php

namespace App\Events;

use App\Traits\HasCollection;

class UserResourceEvent
{
    use HasCollection;

    public function getUnits(): array
    {
        return $this->collection->toArray();
    }

    public function addUnit(string $key, mixed $unit)
    {
        $this->collection->put($key, $unit);
    }
}