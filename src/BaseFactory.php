<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Faker\Factory as FakerFactory;
use Illuminate\Support\Collection;

abstract class BaseFactory implements FactoryInterface
{

    /**
     * @var string
     */
    protected $modelClass;

    private $relatedModel;

    /**
     * @var string
     */
    private $relatedModelRelationshipName;

    public static function new(): self
    {
        return new static;
    }

    public function create(array $extra = [])
    {
        $model = $this->modelClass::create(array_merge($this->getData(FakerFactory::create()), $extra));

        if ($this->relatedModel) {
            $model->{$this->relatedModelRelationshipName}()
                ->saveMany($this->relatedModel);
        }

        return $model;

    }

    public function times(int $times, array $extra = []): Collection
    {
        return collect()
            ->times($times)
            ->transform(function() use ($extra) {
                return $this->create($extra);
           });
    }

    public function with(string $relatedModelClass, string $relationshipName, int $times = 1)
    {
        $this->relatedModel =$this->getFactoryFromClassName($relatedModelClass)
                ->times($times);
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
