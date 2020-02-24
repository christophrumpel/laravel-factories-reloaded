<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Faker\Factory as FakerFactory;
use Illuminate\Support\Collection;

abstract class BaseFactory implements FactoryInterface
{

    protected string $modelClass;

    private $relatedModel;

    private string $relatedModelRelationshipName;

    public static function new(): self
    {
        return new static;
    }

    protected function build(array $extra = [], string $creationType = 'create')
    {
        $model = $this->modelClass::$creationType(array_merge($this->getData(FakerFactory::create()), $extra));

        if ($this->relatedModel) {
            $model->{$this->relatedModelRelationshipName}()
                ->saveMany($this->relatedModel);
        }

        return $model;
    }

    public function times(int $times): CollectionFactory
    {
        $collectionData = collect()
            ->times($times)
            ->map(function ($key) {
                return $this->getData(FakerFactory::create());
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

    private function getFactoryFromClassName(string $className): FactoryInterface
    {
        $baseClassName = (new \ReflectionClass($className))->getShortName();
        $factoryClass = config('factories-reloaded.factories_namespace').'\\'.$baseClassName.'Factory';

        return new $factoryClass;
    }
}
