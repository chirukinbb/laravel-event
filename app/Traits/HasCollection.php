<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait HasCollection
{

    private Collection $collection;

    public function __construct()
    {
        $this->collection = collect();
    }

    public function getUnits(): array
    {
        return $this->collection->map(fn(\UnitEnum $unit) => $unit->value)->toArray();
    }

    public function addUnits(array $units)
    {
        collect($units)->each(function (\UnitEnum $enum) {
            $this->collection->push($enum);
        });
    }
}