<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Illuminate\Support\Collection;
use Faker\Factory as FakerFactory;

class CollectionFactory
{

    private string $modelClass;

    private int $times;

    private array $modelData;

    public function __construct(string $modelClass, int $times, array $modelData)
    {
        $this->modelClass = $modelClass;
        $this->times = $times;
        $this->modelData = $modelData;
    }

    public function create(array $extra = []): Collection
    {
        return $this->build($extra);
    }

    public function make(array $extra = []): Collection
    {
        return $this->build($extra, 'make');
    }

    private function build(array $extra = [], string $creationType = 'create'): Collection
    {
        return collect()
            ->times($this->times)
            ->transform(fn() => $this->modelClass::$creationType(array_merge($this->modelData, $extra)));
    }
}
