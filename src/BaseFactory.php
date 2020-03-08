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
        $faker = FakerFactory::create(config('app.faker_locale', 'en_US'));
        $model = $this->modelClass::$creationType(array_merge($this->getData($faker), $extra));

        if ($this->relatedModel) {
            $model->{$this->relatedModelRelationshipName}()
                ->saveMany($this->relatedModel);
        }

        return $model;
    }

    public function times(int $times): CollectionFactory
    {
        $faker = FakerFactory::create(config('app.faker_locale', 'en_US'));

        $collectionData = collect()
            ->times($times)
            ->map(function ($key) use ($faker) {
                return $this->getData($faker);
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
        $baseClassName = (new \ReflectionClass($className))->getShortName();
        $factoryClass = config('factories-reloaded.factories_namespace').'\\'.$baseClassName.'Factory';

        return new $factoryClass;
    }
}
