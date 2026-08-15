<?php

namespace App\Traits;

use App\Models\UserAPI;
use Illuminate\Support\Collection;

trait ModularResource
{

    private Collection $collection;
    public UserAPI $user;

    public function __construct(public $resource = null)
    {
        $this->collection = collect();
    }

    public function getUnits(): array
    {
        return $this->collection->toArray();
    }

    public function addUnit(string $key, mixed $unit)
    {
        $this->collection->put($key, $unit);
    }
}