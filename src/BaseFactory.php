<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Faker\Factory;
use Illuminate\Support\Collection;

abstract class BaseFactory implements FactoryInterface
{

    protected string $modelClass;

    protected array $stateData = [];

    public static function new(): self
    {
        return new static;
    }

    public function create(array $extra = [])
    {
        return $this->modelClass::create(array_merge($this->getData(Factory::create()), $extra, $this->stateData));
    }

    public function times(int $times, array $extra = []): Collection
    {
        return collect()
            ->times($times)
            ->transform(fn() => $this->create($extra));
    }
}
