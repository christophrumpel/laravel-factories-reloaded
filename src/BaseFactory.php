<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Support\Collection;
use ReflectionClass;

abstract class BaseFactory implements FactoryInterface
{
    use TranslatesFactoryData;

    protected string $modelClass;

    private Collection $relatedModels;

    private string $relatedModelRelationshipName;

    private Generator $faker;

    private array

 $overwriteDefaults = [];

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
        $this->relatedModels = collect();
    }

    /** @return static */
    public static function new(): self
    {
        $faker = FakerFactory::create(config('app.faker_locale', 'en_US'));

        return new static($faker);
    }

    protected function build(array $extra = [], string $creationType = 'create')
    {
        $modelData = $this->prepareModelData($creationType, $this->getDefaults($this->faker));
        $model = $this->modelClass::$creationType(array_merge($modelData, $this->overwriteDefaults, $extra));

        if ($this->relatedModels->isEmpty()) {
            return $model;
        }

        if ($creationType === 'create') {
            $model->{$this->relatedModelRelationshipName}()
                    ->saveMany($this->relatedModels);

            return $model;
        }

        return $model->setRelation($this->relatedModelRelationshipName, $this->relatedModels);
    }

    public function times(int $times = 1): CollectionFactory
    {
        $collectionData = collect()
            ->times($times, function ($key) {
                return array_merge($this->getDefaults($this->faker), $this->overwriteDefaults);
            });

        return new CollectionFactory($this->modelClass, $times, $collectionData);
    }

    public function with(string $relatedModelClass, string $relationshipName, int $times = 1)
    {
        $clone = clone $this;

        $clone->relatedModels = $this->getFactoryFromClassName($relatedModelClass)
            ->times($times)
            ->make();
        $clone->relatedModelRelationshipName = $relationshipName;

        return $clone;
    }

    public function overwriteDefaults(array $attributes): self
    {
        $this->overwriteDefaults = $attributes;

        return $this;
    }

    private function getFactoryFromClassName(string $className): FactoryInterface
    {
        $baseClassName = (new ReflectionClass($className))->getShortName();
        $factoryClass = config('factories-reloaded.factories_namespace').'\\'.$baseClassName.'Factory';

        return new $factoryClass($this->faker);
    }
}
