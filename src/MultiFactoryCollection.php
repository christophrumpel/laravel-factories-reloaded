<?php
namespace Christophrumpel\LaravelFactoriesReloaded;

use Illuminate\Support\Collection;

class MultiFactoryCollection{
    protected Collection $factories;

    public function __construct(Collection $factories)
    {
        $this->factories = $factories;
    }

    public function create()
    {
        return $this->factories->map(function($factory) {
            return $factory->create();
        });
    }

    public function make()
    {
        return $this->factories->map(function($factory) {
            return $factory->make();
        });
    }
}
