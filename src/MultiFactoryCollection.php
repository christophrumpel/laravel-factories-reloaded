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

    public function create(): Collection
    {
        return $this->factories->map->create();
    }

    public function make(): Collection
    {
        return $this->factories->map->make();
    }
}
