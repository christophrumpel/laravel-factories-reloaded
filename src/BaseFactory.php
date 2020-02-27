<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Faker\Factory as FakerFactory;
use Illuminate\Support\Collection;

abstract class BaseFactory implements FactoryInterface
{

    protected string $modelClass;

    private $relatedModel;

    private string $relatedModelRelationshipName;

    private array $overwrites = [];

    public static function new(): self
    {
        return new static;
    }

    protected function build(array $extra = [], string $creationType = 'create')
    {
        $model = $this->modelClass::$creationType(array_merge($this->getData(FakerFactory::create()), $this->overwrites, $extra));

        if ($this->relatedModel) {
            $model->{$this->relatedModelRelationshipName}()
                ->saveMany($this->relatedModel);
        }

        return $model;
    }

    public function times(int $times = 1): CollectionFactory
    {
        $collectionData = collect()
            ->times($times)
            ->map(function ($key) {
                return array_merge($this->getData(FakerFactory::create()), $this->overwrites);
            });

        return new CollectionFactory($this->modelClass, $times, $collectionData);
    }

    public function with(string $relatedModelClass, string $relationshipName, int $times = 1)
    {
        $this->relatedModel = $this->getFactoryFromClassName($relatedModelClass)
            ->times($times)
            ->make();
        $this->relatedModelRelationshipName = $relationshipName;

        return $this;
    }


    public function overwrite(array $attributes): self
    {
        $this->overwrites = $attributes;

        return $this;
    }

    private function getFactoryFromClassName(string $className): FactoryInterface
    {
        $baseClassName = (new \ReflectionClass($className))->getShortName();
        $factoryClass = config('factories-reloaded.factories_namespace').'\\'.$baseClassName.'Factory';

        return new $factoryClass;
    }
}
