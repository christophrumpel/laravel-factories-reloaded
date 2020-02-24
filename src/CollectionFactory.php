<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Illuminate\Support\Collection;

class CollectionFactory
{

    private string $modelClass;

    private int $times;

    private Collection $modelsDefaultData;

    public function __construct(string $modelClass, int $times, Collection $modelData)
    {
        $this->modelClass = $modelClass;
        $this->times = $times;
        $this->modelsDefaultData = $modelData;
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
            ->transform(fn($value, $key) => $this->modelClass::$creationType(array_merge($this->modelsDefaultData[$key], $extra)));
    }
}
