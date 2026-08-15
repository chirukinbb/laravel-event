<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait ModularResource
{

    private Collection $collection;

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