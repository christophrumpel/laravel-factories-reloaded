<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Support\Collection;
use ReflectionClass;

abstract class BaseFactory implements FactoryInterface
{

    protected string $modelClass;

    private $relatedModel;

    private string $relatedModelRelationshipName;

    private Generator $faker;

    public static function new(): self
    {
        $factory = new static;
        $factory->faker = FakerFactory::create(config('app.faker_locale', 'en_US'));

        return $factory;
    }

    protected function build(array $extra = [], string $creationType = 'create')
    {
        $model = $this->modelClass::$creationType(array_merge($this->getData($this->faker), $extra));

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
                return $this->getData($this->faker);
            });

        return new CollectionFactory($this->modelClass, $times, $collectionData);
    }

    public function with(string $relatedModelClass, string $relationshipName, int $times = 1)
    {
        $clone = clone $this;

        $clone->relatedModel = $this->getFactoryFromClassName($relatedModelClass)
            ->times($times)
            ->make();
        $clone->relatedModelRelationshipName = $relationshipName;

        return $clone;
    }

    private function getFactoryFromClassName(string $className): FactoryInterface
    {
        $baseClassName = (new ReflectionClass($className))->getShortName();
        $factoryClass = config('factories-reloaded.factories_namespace').'\\'.$baseClassName.'Factory';

        return new $factoryClass;
    }
}
