<?php
namespace Christophrumpel\LaravelFactoriesReloaded;

use Illuminate\Support\Collection;

class MultiFactoryCollection
{
    protected Collection $factories;

    public function __construct(Collection $factories)
    {
        $this->factories = $factories;
    }

    public function create(array $extra = []): Collection
    {
        return $this->factories->map->create($extra);
    }

    public function make(array $extra = []): Collection
    {
        return $this->factories->map->make($extra);
    }
}
