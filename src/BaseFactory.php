<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Faker\Factory as FakerFactory;
use Illuminate\Support\Collection;

abstract class BaseFactory implements FactoryInterface
{

    protected string $modelClass;

    private array $relatedModel = [];

    public static function new(): self
    {
        return new static;
    }

    public function create(array $extra = [])
    {
        $model = $this->modelClass::create(array_merge($this->getData(FakerFactory::create()), $extra));

        if($this->relatedModel)
        {
            $model->{$this->relatedModel[1]}()->save($this->relatedModel[0]);
        }

        return $model;

    }

    public function times(int $times, array $extra = []): Collection
    {
        return collect()
            ->times($times)
            ->transform(fn() => $this->create($extra));
    }

    public function with(string $relatedModelClass, $relationshipName)
    {
        $this->relatedModel = [$this->getFactoryFromClassName($relatedModelClass)
            ->create(), $relationshipName];

        return $this;
    }

    private function getFactoryFromClassName(string $className)
    {
        $baseClassName = (new \ReflectionClass($className))->getShortName();

        $factoryClass = config('factories-reloaded.factories_namespace') . '\\' . $baseClassName. 'Factory';

        return $factoryClass::new();
    }
}
