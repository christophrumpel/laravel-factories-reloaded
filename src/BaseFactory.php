<?php

namespace Christophrumpel\LaravelFactoriesReloaded;

use Faker\Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use ReflectionClass;

abstract class BaseFactory implements FactoryInterface
{
    use TranslatesFactoryData;

    protected string $modelClass;

    protected bool $immutable = false;

    protected Collection $relatedModelFactories;

    protected Generator $faker;

    protected array $overwriteDefaults = [];

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
        $this->relatedModelFactories = collect();
    }

    /** @return static */
    public static function new(): self
    {
        $faker = app(Generator::class);

        return new static($faker);
    }

    /** @return static */
    public function immutable(bool $immutable = true): self
    {
        $this->immutable = $immutable;

        return $this->immutable ? clone $this : $this;
    }

    protected function build(array $extra = [], string $creationType = 'create')
    {
        $modelData = $this->transformModelFields(
            array_merge($this->getDefaults($this->faker), $this->overwriteDefaults, $extra)
        );

        $model = $this->unguardedIfNeeded(fn () => $this->modelClass::$creationType($modelData));

        if ($this->relatedModelFactories->isEmpty()) {
            return $model;
        }

        return $this->buildRelationsForModel($model, $creationType);
    }

    protected function unguardedIfNeeded(\Closure $closure)
    {
        if (! config('factories-reloaded.unguard_models')) {
            return $closure();
        }

        return $this->modelClass::unguarded($closure);
    }

    public function times(int $times = 1): MultiFactoryCollection
    {
        return new MultiFactoryCollection(collect()->times($times, function () {
            return clone $this;
        }));
    }

    /** @return static */
    public function with(string $relatedModelClass, string $relationshipName, int $times = 1): self
    {
        return $this->withFactory($this->getFactoryFromClassName($relatedModelClass), $relationshipName, $times);
    }

    /** @return static */
    public function withFactory(FactoryInterface $relatedFactory, string $relationshipName, int $times = 1): self
    {
        $clone = clone $this;

        $clone->relatedModelFactories = clone $clone->relatedModelFactories;
        $clone->relatedModelFactories[$relationshipName] ??= collect();
        $clone->relatedModelFactories[$relationshipName] = $clone->relatedModelFactories[$relationshipName]->merge(
            collect()->times($times, fn () => $relatedFactory)
        );

        return $clone;
    }

    /**
     * @param array|callable $attributes
     * @return $this
     */
    public function overwriteDefaults($attributes): self
    {
        $clone = $this->immutable ? clone $this : $this;

        if (is_callable($attributes)) {
            $attributes = $attributes();
        }

        $clone->overwriteDefaults = array_merge($clone->overwriteDefaults, $attributes);

        return $clone;
    }

    protected function getFactoryFromClassName(string $className): FactoryInterface
    {
        $baseClassName = (new ReflectionClass($className))->getShortName();
        $factoryClass = config('factories-reloaded.factories_namespace') . '\\' . $baseClassName . 'Factory';

        return new $factoryClass($this->faker);
    }

    private function buildRelationsForModel(Model $model, string $creationType): Model
    {
        foreach ($this->relatedModelFactories as $relationshipName => $factories) {
            $relation = $model->{$relationshipName}();

            if (method_exists($relation, 'saveMany')) {
                $relatedModels = $factories->map->make();
                $model->setRelation($relationshipName, $relatedModels);

                if ($creationType === 'create') {
                    $relation->saveMany($relatedModels);
                }

                continue;
            }

            if (method_exists($relation, 'associate')) {
                $relatedModels = $factories->map->$creationType();
                $relatedModels->each(fn ($related) => $relation->associate($related));

                if ($creationType === 'create') {
                    $model->save();
                }

                continue;
            }

            throw new InvalidArgumentException('Unsupported relation `' . $relationshipName . '` of ` type `' . get_class($relation) . '`.');
        }

        return $model;
    }
}
